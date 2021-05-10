<?php
/**
 * Author: BERARD Adrien
 * Author URI: https://open.studio
 */

/**
 * @param $dbPlan
 * @return string
 * @throws Exception
 */
function planForm($dbPlan): string
{
    $plan = new Payzen_Subscribers_Plan($dbPlan->id);
//    $checked = isset($_POST['paysubs_plan']) && $_POST['paysubs_plan'] === $plan->getId() ? 'checked' : ' ';
    $checked = ' ';
    return '
    <div class="form-body">
        <div class="form-controls">
            <div class="radio yearly ">
                <input
                    type="radio"
                    class="paysubs_plans"
                    id="paysubs_plan_' . $plan->getId() . '"
                    name="paysubs_plan"
                    value="' . $plan->getId() . '" 
                    ' . $checked . '
                    required
                />
                <label for="paysubs_plan_' . $plan->getId() . '" class="radio-label">
                    <p class="text-wrap">'. wp_unslash($plan->getTitle()) . '</p>
                    <p class="text-wrap"><strong>'. wp_unslash($plan->getDescription()) . '</strong></p>
                </label>
                <p class="price">
                    <strong>
                        <span>' . number_format($plan->getAmount() / 100, 2,',', ' ') . '€</span>
                        <span>' . ucfirst(strtolower($plan->getFrequencyTranslate())) . '</span>
                    </strong>
                </p>
            </div><!-- /.radio -->
        </div><!-- /.form-controls -->
    </div><!-- /.form-body -->
    ';
}

/**
 * @param Array $paymentMethods
 * @return string
 */
function paymentMethodFormAjax(Array $paymentMethods): string
{
    $output = '
    <h2>3. '. __('Choose your payment method','payzen-subscribers') . '</h2>
    <div class="form-controls">
    ';
    foreach ($paymentMethods as $key => $paymentMethod){
        $output .= '
        <div class="radio">
            <input type="radio" id="radio_group_card_'.$key.'" name="paysubs_payment_method" value="' . $paymentMethod . '" required/>
            <label for="radio_group_card_'.$key.'" class="radio-label">
                <img src="' .getOverridePublicPath("assets/logo-" . $paymentMethod . ".png") .' " alt="'.$paymentMethod.'" />
            </label>
        </div><!-- /.radio -->
        ';
    }
    $output .= '</div><!-- /.form-controls -->';
    return $output;
}

function registrationForm(): string
{
    return  '
	<div>
        <label class="tooltip" for="username">
            <strong>*</strong> ' . __('Login','payzen-subscribers') . '
            <span class="tooltiptext">
                ' . __('Login is require<br class="display">','payzen-subscribers') . '
                ' . __('Please note: your username allows you to connect to the site and is also used as a name when you leave comments.','payzen-subscribers') . '
                <br class="display">
            </span>
        </label>
        <input type="text" id="username" name="username" required value="' . (isset($_POST['username']) ? $_POST['username'] : null) . '">
        ' . displayError('username_length') . '
        ' . displayError('username') . '
        ' . displayError('username_invalid') . '
	</div>
	<div>
        <label class="tooltip" for="password"><strong>*</strong> ' . __('Password','payzen-subscribers') . '
            <span  class="tooltiptext">
                ' . __('Password is require','payzen-subscribers') . '
            </span>
        </label>
        <input type="password" id="password" name="password" required value="' . (isset($_POST['password']) ? $_POST['password'] : null) . '">
        ' . displayError('password') . '
	</div>
	<div>
        <label class="tooltip" for="password_confirm"><strong>*</strong> ' . __('Password confirmation','payzen-subscribers') . '
            <span  class="tooltiptext">
                ' . __('Password confirmation is require','payzen-subscribers') . '
            </span>
        </label>
        <input type="password" id="password_confirm" name="password_confirm" required value="' . (isset($_POST['password_confirm']) ? $_POST['password_confirm'] : null) . '">
        ' . displayError('password_confirm') . '
	</div>
	<div>
        <label class="tooltip" for="email"><strong>*</strong> ' . __('Email','payzen-subscribers') . '
            <span class="tooltiptext">
                ' . __('Email is require','payzen-subscribers') . '
            </span>
        </label>
        <input type="text" id="email" name="email" required value="' . (isset($_POST['email']) ? $_POST['email'] : null) . '">
        ' . displayError('email') . '
        ' . displayError('email_invalid') . '
	</div>
	<div>
        <label class="tooltip" for="email_confirm"><strong>*</strong> ' . __('Email confirmation','payzen-subscribers') . '
            <span class="tooltiptext">
                ' . __('Email confirmation is require','payzen-subscribers') . '
            </span>
        </label>
        <input type="text" id="email_confirm" name="email_confirm" required value="' . (isset($_POST['email_confirm']) ? $_POST['email_confirm'] : null) . '">
        ' . displayError('email_confirm') . '
	</div>
	<div>
        <label for="firstname"><strong>*</strong>' . __('First Name','payzen-subscribers') . '</label>
        <input type="text" id="firstname" name="fname" required value="' . (isset($_POST['fname']) ? $_POST['fname'] : null) . '">
	</div>
	<div>
        <label for="lastname"><strong>*</strong>' . __('Last Name','payzen-subscribers') . '</label>
        <input type="text"  id="lastname" name="lname" required value="' . (isset($_POST['lname']) ? $_POST['lname'] : null) . '">
	</div>
	<div>
        <span class="required-fields-note">* '. __('Required fields','payzen-subscribers') .'</span>
    </div>
	<div>
	    <input type="checkbox" id="receive_newsletter" name="receive_newsletter" value="true">
        <label for="receive_newsletter">' . sprintf(__('I agree to receive %s\'s newsletter and alerts.','payzen-subscribers'), get_bloginfo('name')) . '</label>
    </div>
	<div>
	    <input type="checkbox" id="paysubs_terms" name="paysubs_terms" value="true" required>
        <label for="paysubs_terms">' . sprintf(__('I have read and accept the General Conditions of Sale and Use (1) and the Comments Charter (2)','payzen-subscribers'), get_bloginfo('name')) . '</label>
    </div>
    <div class="form-notes">
        <p>
            (1) <a target="_blank" href="' . getOverridePublicPath('assets/xxx.pdf') .'">'. __('Download the General Conditions of Sale and Use','payzen-subscribers') . '</a>
        </p>
        <p>
            (2) <a target="_blank" href="' . getOverridePublicPath('assets/CHARTE-DES-COMMENTAIRES.pdf') .'">'. __('Download the comments charter','payzen-subscribers') . '</a>
        </p>
    </div>
	';
}

/**
 * Display wp errors
 * @param $message
 * @return string
 */
function displayError($message): string
{
    global $registrationErrors;
    if($registrationErrors->has_errors()) {
        $error = $registrationErrors->get_error_message($message);
        if (null !== $error) {
            return '<span class="error-message">' . $error . '</span>';
        }
    }
    return '';
}

function isAllReadyActivePlan(): string
{
    return '
        <div>
            '. __('You already have a plan','payzen-subscribers') .'
        </div>
    ';
}

/**
 * Display a nice error code block
 * @param $response
 */
function payzen_display_error($response)
{
    $error = $response['answer'];
    ?>
    <p style="font-family: Verdana,sans-serif;font-size:18px;color:#a52828;font-weight: BOLD;">web-service call returns an error:</p>
    <table style="border: 1px solid black;border-collapse: collapse;font-family: Verdana,sans-serif;line-height: 1.5;text-align: left;padding: 8px;">
        <tr style="background-color:#a52828;color:white;">
            <th style="padding: 8px;">Field</th>
            <th style="padding: 8px;">value</th>
        </tr>
        <tr style="background-color: #f2f2f2">
            <td style="padding: 8px;">web service:</td>
            <td style="padding: 8px;"><?php echo $response['webService'];?></td>
        </tr>
        <tr>
            <td style="padding: 8px;">errorCode:</td>
            <td style="padding: 8px;"><?php echo $error['errorCode'];?></td>
        </tr>
        <tr style="background-color: #f2f2f2">
            <td style="padding: 8px;">errorMessage:</td>
            <td style="padding: 8px;"><?php echo $error['errorMessage'];?></td>
        </tr>
        <tr>
            <td style="padding: 8px;">detailedErrorCode:</td>
            <td style="padding: 8px;"><?php echo $error['detailedErrorCode'];?></td>
        </tr>
        <tr style="background-color: #f2f2f2">
            <td style="padding: 8px;">detailedErrorMessage:</td>
            <td style="padding: 8px;"><?php echo $error['detailedErrorMessage'];?></td>
        </tr>
    </table>
    <?php
}

/**
 * Display a nice signature error
 * @param $tr_uuid
 * @param $sha_key
 * @param $expected
 * @param $received
 */
function signature_error($tr_uuid, $sha_key, $expected, $received)
{
    ?>
    <p style="font-family: Verdana,sans-serif;font-size:18px;color:#a52828;font-weight: BOLD;">SHA256 validation failed</p>
    <table style="border: 1px solid black;border-collapse: collapse;font-family: Verdana,sans-serif;line-height: 1.5;text-align: left;padding: 8px;">
        <tr style="background-color:#a52828;color:white;">
            <th style="padding: 8px;">Field</th>
            <th style="padding: 8px;">value</th>
        </tr>
        <tr style="background-color: #f2f2f2">
            <td style="padding: 8px;">transaction uuid:</td>
            <td style="padding: 8px;"><?php echo $tr_uuid;?></td>
        </tr>
        <tr>
            <td style="padding: 8px;">sha key:</td>
            <td style="padding: 8px;"><?php echo $sha_key;?></td>
        </tr>
        <tr style="background-color: #f2f2f2">
            <td style="padding: 8px;">expected value (calculated):</td>
            <td style="padding: 8px;"><?php echo $expected;?></td>
        </tr>
        <tr>
            <td style="padding: 8px;">recieved value (from POST):</td>
            <td style="padding: 8px;"><?php echo $received;?></td>
        </tr>
    </table>
    <?php
}