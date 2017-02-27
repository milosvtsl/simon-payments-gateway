<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 8/29/2016
 * Time: 1:25 PM
 */
namespace User\View;

use System\Arrays\TimeZones;
use User\Mail\UserWelcomeEmail;
use User\Model\UserRow;
use User\Session\SessionManager;
use View\AbstractView;

class AddUserView extends AbstractView
{

    public function renderHTMLBody(Array $params) {
        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();
        if(!$SessionUser->hasAuthority('ADMIN', 'SUB_ADMIN')) {
            // Only admins may add other users
            $SessionManager->setMessage("Unable to add user. Permission required: ADMIN or SUB_ADMIN");

            $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';
            header("Location: {$baseHREF}user?action=add&message=Unable to manage integration: Admin required");
            die();
        }

        // Render Page

        /**
         * @var UserView $this
         * @var \PDOStatement $UserQuery
         * @var UserRow $User
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


                <?php if($SessionManager->hasMessage()) echo "<h5>", $SessionManager->popMessage(), "</h5>"; ?>

                <form class="form-add-user themed" method="POST" action="user/add.php">
                    <input type="hidden" name="action" value="add" />

                    <fieldset>
                        <div class="legend">Create New User</div>
                        <table class="table-user-info themed striped-rows" style="width: 100%;">
                            <tr>
                                <th colspan="2" class="section-break">Required Fields</th>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Username</td>
                                <td><input type="text" name="username" value="" required /></td>
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
                                        $found = false;
                                        $curtimezone = date('P');
                                        foreach(TimeZones::$TimeZones as $timezone => $name) {
                                            try {
                                                $time = new \DateTime(NULL, new \DateTimeZone($timezone));
                                                $name .= " (" . $time->format('g:i A') . ")";
                                                $selected = $time->format('P') === $curtimezone ? ' selected="selected"' : '';
                                                if($selected) {
                                                    if($found) $selected = '';
                                                    $found = true;
                                                }
                                                echo "\n\t\t\t<option value='{$timezone}'{$selected}>{$name}</option>";
                                            } catch (\Exception $ex) {
                                                // Only show available timezones. Where did greenland go anyway
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th colspan="2" class="section-break">Optional Fields</th>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Password</td>
                                <td><input type="password" name="password" value="" autocomplete="off" /></td>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Password Confirm</td>
                                <td><input type="password" name="password_confirm" value="" autocomplete="off" /></td>
                            </tr>
                            <tr>
                                <th colspan="2" class="section-break">Submit Form</th>
                            </tr>
                            <tr class="row-<?php echo ($odd=!$odd)?'odd':'even';?>">
                                <td class="name">Send Email Confirmation</td>
                                <td><input type="checkbox" checked="checked" name="send_email_welcome" value="1" /></td>
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
        <?php
    }

    public function processFormRequest(Array $post) {
        $baseHREF = defined("BASE_HREF") ? \BASE_HREF : '';

        $SessionManager = new SessionManager();
        $SessionUser = $SessionManager->getSessionUser();
        if(!$SessionUser->hasAuthority('ADMIN', 'SUB_ADMIN')) {
            // Only admins may add users
            $SessionManager->setMessage("Unable to add user. Permission required: ADMIN or SUB_ADMIN");
                header("Location: {$baseHREF}user?action=add&message=Unable to manage integration: Admin required");
                die();
        }

        try {
            $password = '****';
            if(empty($post['password'])) {
                $password = $this->randomPassword();
                $post['password'] = $password;
                $post['password_confirm'] = $password;
            }
            $User = UserRow::createNewUser($post, $SessionUser);
            $SessionManager->setMessage("User created successfully: " . $User->getUID());

            if(!empty($post['send_email_welcome'])) {
                $Email = new UserWelcomeEmail($User, $password);
                $Email->send();
            }
            header("Location: {$baseHREF}user?uid={$User->getUID()}");
            die();

        } catch (\InvalidArgumentException $ex) {
            $SessionManager->setMessage("User creation failed: " . $ex->getMessage());
            header("Location: {$baseHREF}user/add.php");
            die();
        }

    }

    function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}