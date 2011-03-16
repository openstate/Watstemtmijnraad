START TRANSACTION;

UPDATE rs_raadsstukken_submitters SET politician = 320 WHERE politician = 328 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_raadsstukken_submitters WHERE politician = 320);
INSERT INTO rs_votes(politician, raadsstuk, vote) SELECT 320, raadsstuk, vote FROM rs_votes WHERE politician = 328 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_votes WHERE politician = 320);
DELETE FROM rs_votes WHERE politician = 328;
DELETE FROM pol_politicians WHERE id = 328;


-- Inserting new function
INSERT INTO pol_politician_functions (politician, party, region, category, time_start, time_end, description)
				         VALUES (324, 6, 319, -1, '2007-01-27', 'infinity', '');
UPDATE rs_raadsstukken_submitters SET politician = 324 WHERE politician = 330 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_raadsstukken_submitters WHERE politician = 324);
INSERT INTO rs_votes(politician, raadsstuk, vote) SELECT 324, raadsstuk, vote FROM rs_votes WHERE politician = 330 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_votes WHERE politician = 324);
DELETE FROM rs_votes WHERE politician = 330;
DELETE FROM pol_politicians WHERE id = 330;


UPDATE rs_raadsstukken_submitters SET politician = 196 WHERE politician = 331 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_raadsstukken_submitters WHERE politician = 196);
INSERT INTO rs_votes(politician, raadsstuk, vote) SELECT 196, raadsstuk, vote FROM rs_votes WHERE politician = 331 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_votes WHERE politician = 196);
DELETE FROM rs_votes WHERE politician = 331;
DELETE FROM pol_politicians WHERE id = 331;


UPDATE rs_raadsstukken_submitters SET politician = 212 WHERE politician = 332 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_raadsstukken_submitters WHERE politician = 212);
INSERT INTO rs_votes(politician, raadsstuk, vote) SELECT 212, raadsstuk, vote FROM rs_votes WHERE politician = 332 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_votes WHERE politician = 212);
DELETE FROM rs_votes WHERE politician = 332;
DELETE FROM pol_politicians WHERE id = 332;


UPDATE rs_raadsstukken_submitters SET politician = 160 WHERE politician = 642 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_raadsstukken_submitters WHERE politician = 160);
INSERT INTO rs_votes(politician, raadsstuk, vote) SELECT 160, raadsstuk, vote FROM rs_votes WHERE politician = 642 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_votes WHERE politician = 160);
DELETE FROM rs_votes WHERE politician = 642;
DELETE FROM pol_politicians WHERE id = 642;


UPDATE rs_raadsstukken_submitters SET politician = 201 WHERE politician = 643 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_raadsstukken_submitters WHERE politician = 201);
INSERT INTO rs_votes(politician, raadsstuk, vote) SELECT 201, raadsstuk, vote FROM rs_votes WHERE politician = 643 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_votes WHERE politician = 201);
DELETE FROM rs_votes WHERE politician = 643;
DELETE FROM pol_politicians WHERE id = 643;


UPDATE rs_raadsstukken_submitters SET politician = 219 WHERE politician = 644 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_raadsstukken_submitters WHERE politician = 219);
INSERT INTO rs_votes(politician, raadsstuk, vote) SELECT 219, raadsstuk, vote FROM rs_votes WHERE politician = 644 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_votes WHERE politician = 219);
DELETE FROM rs_votes WHERE politician = 644;
DELETE FROM pol_politicians WHERE id = 644;


UPDATE rs_raadsstukken_submitters SET politician = 197 WHERE politician = 645 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_raadsstukken_submitters WHERE politician = 197);
INSERT INTO rs_votes(politician, raadsstuk, vote) SELECT 197, raadsstuk, vote FROM rs_votes WHERE politician = 645 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_votes WHERE politician = 197);
DELETE FROM rs_votes WHERE politician = 645;
DELETE FROM pol_politicians WHERE id = 645;


UPDATE rs_raadsstukken_submitters SET politician = 309 WHERE politician = 649 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_raadsstukken_submitters WHERE politician = 309);
INSERT INTO rs_votes(politician, raadsstuk, vote) SELECT 309, raadsstuk, vote FROM rs_votes WHERE politician = 649 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_votes WHERE politician = 309);
DELETE FROM rs_votes WHERE politician = 649;
DELETE FROM pol_politicians WHERE id = 649;


-- Joining ranges: 1
UPDATE pol_politician_functions SET time_end = '2008-12-15' WHERE id = 418;
UPDATE rs_raadsstukken_submitters SET politician = 179 WHERE politician = 651 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_raadsstukken_submitters WHERE politician = 179);
INSERT INTO rs_votes(politician, raadsstuk, vote) SELECT 179, raadsstuk, vote FROM rs_votes WHERE politician = 651 AND raadsstuk NOT IN (SELECT raadsstuk FROM rs_votes WHERE politician = 179);
DELETE FROM rs_votes WHERE politician = 651;
DELETE FROM pol_politicians WHERE id = 651;


ROLLBACK;
