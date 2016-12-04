<?php
use System\Arrays\TimeZones;
/**
 * @var \User\View\UserView $this
 * @var PDOStatement $UserQuery
 * @var \User\Model\UserRow $User
 **/
$odd = false;

$Theme = $this->getTheme();
$Theme->addPathURL('user',          'Users');
$Theme->addPathURL('user/add.php',  'Add New User');
$Theme->renderHTMLBodyHeader();
$Theme->printHTMLMenu('user-add');

?>

    <article class="themed">
        <section class="content">


            <?php if($this->hasMessage()) echo "<h5>", $this->getMessage(), "</h5>"; ?>

            <form class="form-add-user themed" method="POST" action="user/add.php">
                <input type="hidden" name="action" value="add" />

                <fieldset style="display: inline-block;">
                    <div class="legend">New User Fields</div>
                    <table class="table-user-info themed">
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Username</td>
                            <td><input type="text" name="username" value="" required /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Password</td>
                            <td><input type="password" name="password" value="" autocomplete="off" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Password Confirm</td>
                            <td><input type="password" name="password_confirm" value="" autocomplete="off" /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Email</td>
                            <td><input type="email" name="email" value="" required /></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">First Name</td>
                            <td><input type="text" name="fname" value="" required/></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Last Name</td>
                            <td><input type="text" name="lname" value="" required/></td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">User Timezone</td>
                            <td>
                                <select name="timezone" value="" required>
                                    <?php
                                    $curtimezone = date_default_timezone_get();
                                    foreach(TimeZones::$TimeZones as $timezone => $name) {
                                        try {
                                            $time = new \DateTime(NULL, new \DateTimeZone($timezone));
                                            $name .= " (" . $time->format('g:i A') . ")";
                                            $selected = $timezone === $curtimezone ? ' selected="selected"' : '';
                                            echo "\n\t\t\t<option value='{$timezone}'{$selected}>{$name}</option>";
                                        } catch (Exception $ex) {
                                            // Only show available timezones. Where did greenland go anyway
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                            <td class="name">Create User</td>
                            <td><input type="submit" value="Create" class="themed"/></td>
                        </tr>
                    </table>
                </fieldset>
            </form>
        </section>
    </article>