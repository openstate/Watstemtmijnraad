BEGIN;

ALTER TABLE ONLY rs_votes
	ADD COLUMN party INTEGER;

ALTER TABLE ONLY rs_votes
	DROP CONSTRAINT rs_votes_politician_fkey;

ALTER TABLE ONLY rs_votes
    ADD CONSTRAINT rs_votes_party_fkey FOREIGN KEY (party) REFERENCES pol_parties(id) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY rs_votes
    ADD CONSTRAINT rs_votes_politician_fkey FOREIGN KEY (politician) REFERENCES pol_politicians(id) ON UPDATE CASCADE ON DELETE CASCADE;

CREATE FUNCTION get_party(raadsstuk_id integer, politician_id integer) RETURNS integer
	AS $$DECLARE
	party_id INTEGER;
BEGIN
	party_id := party FROM pol_politician_functions f JOIN rs_raadsstukken r ON f.region = r.region
		WHERE f.politician = politician_id AND r.id = raadsstuk_id AND r.vote_date BETWEEN f.time_start AND f.time_end ORDER BY f.id LIMIT 1;
	RETURN party_id;
END;$$
	LANGUAGE plpgsql;

DROP FUNCTION pol_politician_functions_trigger() CASCADE;
CREATE FUNCTION pol_politician_functions_trigger() RETURNS "trigger"
	AS $$BEGIN
	UPDATE rs_votes SET party = (SELECT get_party(rs_votes.raadsstuk, OLD.politician)) WHERE politician = OLD.politician;
	DELETE FROM rs_votes WHERE politician = OLD.politician AND party IS NULL;
	IF (TG_OP = 'UPDATE') THEN
		IF (OLD.politician != NEW.politician) THEN
			UPDATE rs_votes SET party = (SELECT get_party(rs_votes.raadsstuk, NEW.politician)) WHERE politician = NEW.politician;
			DELETE FROM rs_votes WHERE politician = NEW.politician AND party IS NULL;
		END IF;
	END IF;
	RETURN NULL;
END;$$
	LANGUAGE plpgsql;

CREATE TRIGGER pol_politician_functions_trigger
    AFTER DELETE OR UPDATE ON pol_politician_functions
    FOR EACH ROW
    EXECUTE PROCEDURE pol_politician_functions_trigger();

DROP FUNCTION rs_votes_trigger() CASCADE;
CREATE FUNCTION rs_votes_trigger() RETURNS "trigger"
    AS $$BEGIN
	IF (TG_OP = 'DELETE') THEN
		IF (OLD.party IS NOT NULL) THEN
			PERFORM update_party_cache(OLD.raadsstuk, OLD.party);
		END IF;
		PERFORM update_vote_cache(OLD.raadsstuk);
	ELSIF (TG_OP = 'INSERT') THEN
		UPDATE rs_votes SET party = (SELECT get_party(rs_votes.raadsstuk, NEW.politician)) WHERE id = NEW.id;
	ELSIF (TG_OP = 'UPDATE') THEN
		IF (NEW.party IS NULL) THEN
			RETURN NULL;
		END IF;
		IF (OLD.raadsstuk != NEW.raadsstuk OR OLD.party != NEW.party) THEN
			PERFORM update_party_cache(OLD.raadsstuk, OLD.party);
			PERFORM update_party_cache(NEW.raadsstuk, NEW.party);
			IF (OLD.raadsstuk != NEW.raadsstuk) THEN
				PERFORM update_vote_cache(OLD.raadsstuk);
				PERFORM update_vote_cache(NEW.raadsstuk);
			END IF;
		END IF;
	END IF;
	RETURN NULL;
END;$$
    LANGUAGE plpgsql;

CREATE TRIGGER rs_votes_trigger
    AFTER INSERT OR DELETE OR UPDATE ON rs_votes
    FOR EACH ROW
    EXECUTE PROCEDURE rs_votes_trigger();

DROP FUNCTION update_party_cache(raadsstuk_id integer, politician_id integer);
CREATE FUNCTION update_party_cache(raadsstuk_id integer, party_id integer) RETURNS void
    AS $$DECLARE
	cache RECORD;
BEGIN
	IF (SELECT COUNT(*) FROM rs_party_vote_cache WHERE raadsstuk = raadsstuk_id AND party = party_id) = 0 THEN
		INSERT INTO rs_party_vote_cache (raadsstuk, party) VALUES (raadsstuk_id, party_id);
	END IF;
	SELECT
		SUM((vote = 0)::int) AS vote_0, SUM((vote = 1)::int) AS vote_1, SUM((vote = 2)::int) AS vote_2, SUM((vote = 3)::int) AS vote_3
		INTO cache FROM rs_votes WHERE raadsstuk = raadsstuk_id AND party = party_id;
	UPDATE rs_party_vote_cache
		SET vote_0 = cache.vote_0, vote_1 = cache.vote_1, vote_2 = cache.vote_2, vote_3 = cache.vote_3
		WHERE raadsstuk = raadsstuk_id AND party = party_id;
END;$$
    LANGUAGE plpgsql;

DROP FUNCTION update_vote_cache(raadsstuk_id integer);
CREATE FUNCTION update_vote_cache(raadsstuk_id integer) RETURNS void
    AS $$DECLARE
	cache RECORD;
BEGIN
	SELECT
		SUM((vote = 0)::int) AS vote_0, SUM((vote = 1)::int) AS vote_1, SUM((vote = 2)::int) AS vote_2, SUM((vote = 3)::int) AS vote_3
		INTO cache FROM rs_votes WHERE raadsstuk = raadsstuk_id;
	UPDATE rs_vote_cache
		SET vote_0 = cache.vote_0, vote_1 = cache.vote_1, vote_2 = cache.vote_2, vote_3 = cache.vote_3
		WHERE id = raadsstuk_id;
END;$$
    LANGUAGE plpgsql;

DROP TRIGGER update_party_vote_cache;

UPDATE rs_votes SET party = get_party(raadsstuk, politician);
DELETE FROM rs_votes WHERE party IS NULL;

ROLLBACK;