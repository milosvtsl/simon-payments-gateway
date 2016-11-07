<?php
use System\Arrays\TimeZones;
use Merchant\Model\MerchantRow;
/**
 * @var \User\View\UserView $this
 * @var PDOStatement $UserQuery
 * @var \User\Model\UserRow $User
 **/
$Merchant = new MerchantRow();
$odd = false;
?>

<!-- Page Navigation -->
<nav class="page-menu hide-on-print">
    <a href="/" class="button">Dashboard <div class="submenu-icon submenu-icon-dashboard"></div></a>
    <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_POST_CHARGE')) { ?>
        <a href="transaction/charge.php" class="button<?php echo @$ca['charge']; ?>">Charge<div class="submenu-icon submenu-icon-charge"></div></a>
    <?php } ?>
    <?php if($SessionUser->hasAuthority('ROLE_ADMIN', 'ROLE_SUB_ADMIN')) { ?>
        <a href="merchant?" class="button">Merchants <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="merchant/add.php" class="button current">Add Merchant <div class="submenu-icon submenu-icon-add"></div></a>
        <a href="user?" class="button">Users <div class="submenu-icon submenu-icon-list"></div></a>
        <a href="order?" class="button">Transactions <div class="submenu-icon submenu-icon-list"></div></a>
    <?php } ?>
</nav>


    <article id="article" class="themed">
        <section id="content" class="content">
            <a name='content'></a>

            <!-- Bread Crumbs -->
            <aside class="bread-crumbs">
                <a href="merchant" class="nav_merchant">Merchants</a>
                <a href="merchant/add.php" class="nav_merchant_add">Add New Merchant</a>
            </aside>

            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <form class="form-add-merchant themed" method="POST">
                <input type="hidden" name="action" value="add" />
                <fieldset>
                    <legend>New Merchant Fields</legend>
                    <table class="table-merchant-info themed striped-rows">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Name</td>
                            <td><input type="text" name="name" value="" required /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Short Name</td>
                            <td><input type="text" name="short_name" value="" /></td>
                        </tr>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Main Contact</td>
                            <td><input type="text" name="main_contact" value="" required /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Main Email Contact</td>
                            <td><input type="email" name="main_email_id" value="" required /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">URL</td>
                            <td><input type="text" name="url" value="" required/></td>
                        </tr>
                        <tr>
                            <th colspan="2">Business</th>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Business Type</td>
                            <td>
                                <select name="business_type" required>
                                    <?php
                                    foreach(MerchantRow::$ENUM_BUSINESS_TYPE as $code=>$title)
                                        echo "<option value='", $code, "'",
                                        ($Merchant->getBusinessType() === $code ? ' selected="selected"' : ''),
                                        ">", $title, "</option>\n";
                                    ?>
                                </select>
                            </td>
                        </tr>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Phone Number</td>
                            <td><input type="text" name="telephone" size="32" value="<?php echo $Merchant->getTelephone(); ?>" required /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Address</td>
                            <td><input type="text" name="address1" size="32" value="<?php echo $Merchant->getAddress(); ?>" required /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Address 2</td>
                            <td><input type="text" name="address2" size="32" value="<?php echo $Merchant->getAddress2(); ?>" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">City</td>
                            <td><input type="text" name="city" size="32" value="<?php echo $Merchant->getCity(); ?>" required /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">State</td>
                            <td>
                                <select name="state_id" required>
                                    <?php
                                    $StateQuery = \System\Model\StateRow::queryAll();
                                    foreach($StateQuery as $State)
                                        /** @var \System\Model\StateRow $State */
                                        echo "<option value='", $State->getID(), "'",
                                        ($State->getShortCode() === $Merchant->getRegionCode() ? ' selected="selected"' : ''),
                                        ">", $State->getName(), "</option>\n";
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Zip</td>
                            <td><input type="text" name="zipcode" size="12" value="<?php echo $Merchant->getZipCode(); ?>" required /></td>
                        </tr>

                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Country</td>
                            <td>
                                <select name="country" required>
                                    <?php
                                    foreach(\System\Arrays\Locations::$COUNTRIES as $code => $name)
                                        if(strlen($code) === 3)
                                            echo "<option value='", $code, "'",
                                            ($code === $Merchant->getCountryCode() ? ' selected="selected"' : ''),
                                            ">", $name, "</option>\n";
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Create Merchant</td>
                            <td><input type="submit" value="Create" class="themed" /></td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </section>
    </article>