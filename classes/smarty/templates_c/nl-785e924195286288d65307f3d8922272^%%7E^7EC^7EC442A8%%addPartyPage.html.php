<?php /* Smarty version 2.6.18, created on 2011-01-13 11:28:38
         compiled from /var/www/projects/watstemtmijnraad_hg/pages/admin/appointments/content/addPartyPage.html */ ?>
<h1>Partij toevoegen</h1>

<form method="POST" action="">
    <table class="form">

        <tbody id="extrainfo">
            <tr><th>Naam van de partij: </th><td><input type="text" name="party_new"/></td></tr>
            <tr><th>Heeft de partij een afkorting?</th><td><input type="radio" name="short" value="0"/>Nee &nbsp;<input type="radio" name="short" value="1"/>Ja namelijk: <input type="text" name="short_form"/></td></tr>
            <tr><th>Is de partij een combinatie partij?</th><td><input type="radio" name="combination" id="combi_0" value="0"/>  Nee &nbsp;<input type="radio" id="combi_1" name="combination" value="1"/>Ja </td></tr>
        </tbody>

        <tbody id="combi">
            <tr><th>De partij is een cominatie van:</th>
                <td>
                    <select name="combi[]">
                        <?php $_from = $this->_tpl_vars['parties']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['party']):
?>
                        <option value="<?php echo $this->_tpl_vars['party']->party; ?>
"><?php echo $this->_tpl_vars['party']->party_name; ?>
</option>
                        <?php endforeach; endif; unset($_from); ?>
                    </select>
                </td>
            </tr>
            <tr id="combination">
                <th></th>
                <td>
                    <select name="combi[]">
                        <?php $_from = $this->_tpl_vars['parties']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['party']):
?>
                            <option value="<?php echo $this->_tpl_vars['party']->party; ?>
"><?php echo $this->_tpl_vars['party']->party_name; ?>
</option>
                        <?php endforeach; endif; unset($_from); ?>
                    </select><br/>
                    
                </td>
            </tr>
            <tr><th></th><td>
            <a href="#" id="addMoreParents"/>Nog een relatie toevoegen</a></td></tr>
        </tbody>

        <tbody>
            <tr><td><input type="hidden" name="region" value="<?php echo $this->_tpl_vars['region']; ?>
"/><input type="submit" value="Verzenden"></td></tr>
        </tbody>
        
    </table>
</form>