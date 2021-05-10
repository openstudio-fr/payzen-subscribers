<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://www.openstudio.fr/
 * @since      1.0.0
 *
 * @package    Payzen_Subscribers
 * @subpackage Payzen_Subscribers/public/partials
 */


/**
 * @param $plans
 */
function getFirstStepPaymentForm($plans)
{
?>
<div class="section-registration">
    <form class="form-section-package" action="<?php echo get_permalink( get_the_id() ); ?>#paysubs_payment_anchor_form" method="post" id="paysubs_payment_anchor_form">
        <div class="form-section form-section-plans">
            <h2>1. <?php _e('Choose a plan','payzen-subscribers'); ?></h2>
            <?php foreach ($plans as $key => $plan){
                echo planForm($plan);
            }
            ?>
        </div>
        <?php if(!is_user_logged_in()){ ?>
            <div class="form-section form-section-account">
                <h2>2. <?php _e('Create your account','payzen-subscribers'); ?></h2>
                <?php echo registrationForm(); ?>

            </div><!-- /.form-section -->
        <?php }?>
        <div id="form-section-payment" class="form-section form-section-payment">
            <!-- Ajax display section : paymentMethodFormAjax() -->
        </div><!-- /.form-section -->

        <div class="form-actions alternative-button">
            <button type="submit" class="form-btn form-btn-submit" ><?php _e('Submit','payzen-subscribers'); ?></button>
            <input type="hidden" name="paysubs_action" value="paysubs_payment_cb_step_2">
            <span class="ajax-spinner"></span>
        </div>
    </form>
</div>

<?php
}

function displayPaymentForm(){
    try {
        $response = Payzen_Subscribers_Plan::createPaymentPlan(
            getUserMeta('paysubs_plan')[0],
            "EUR",
            "MyOrderId", //TODO : Add to admin interface
            wp_get_current_user()->get('user_email')
        );

        ?>
        <h2>
            <?php _e('Create payment form','payzen-subscribers'); ?>
            <span class="container"><span class="payzen-secure"><svg
                            width="21px"
                            height="30px"
                            viewBox="0 0 210 297"
                            version="1.1"
                            id="svg8">
                          <defs
                                  id="defs2" />
                          <metadata
                                  id="metadata5">
                          </metadata>
                          <g
                                  id="layer1">
                            <path
                                    style="fill-opacity:1;stroke-width:0.445423"
                                    d="M 101.51083,286.09831 C 66.395371,259.12033 36.705208,229.88367 20.861702,206.68103 c -1.087336,-1.59238 -2.200348,-3.13294 -2.473358,-3.42344 -0.443152,-0.47156 -2.942393,-4.61826 -6.661619,-11.05282 C 7.6359094,185.12731 3.6597079,174.70252 2.0815378,166.91706 L 1.0677787,161.91594 V 100.01443 38.112904 L 3.5980105,36.438419 C 10.085324,32.145163 23.44893,25.717777 32.915579,22.337765 74.345069,7.5455823 125.23279,6.3444615 168.32439,19.141658 c 14.08566,4.183122 29.78982,11.12419 39.08282,17.27422 l 2.56432,1.697026 v 61.901526 61.90151 l -1.02138,5.023 c -1.58694,7.80437 -5.53407,18.15531 -9.63463,25.26583 -4.13964,7.17824 -6.14201,10.35217 -9.55433,15.14442 -15.02974,21.10765 -35.22311,41.85042 -64.33392,66.08424 -6.4914,5.40389 -19.72596,15.72212 -20.02497,15.61231 -0.0579,-0.0212 -1.80911,-1.34762 -3.89147,-2.94743 z m 50.86723,-83.57397 c 0.66134,-0.4488 1.61344,-1.51256 2.11577,-2.36393 0.89997,-1.52535 0.91328,-2.06025 0.91328,-36.68547 0,-34.1916 -0.0238,-35.17724 -0.87948,-36.61066 -0.4837,-0.8102 -1.55251,-1.92689 -2.37513,-2.48154 l -1.49569,-1.00844 -45.40754,0.11767 -45.407547,0.11768 -1.296215,1.01969 c -0.712923,0.56082 -1.660069,1.61799 -2.10477,2.34923 -0.762959,1.25459 -0.808547,3.30139 -0.808547,36.30232 0,33.59526 0.03363,35.03783 0.853806,36.62534 0.845498,1.63652 2.101141,2.71467 3.823143,3.28274 0.489968,0.16162 21.13536,0.26185 45.87865,0.22272 42.81202,-0.0677 45.04595,-0.11068 46.19027,-0.8872 z M 82.922728,101.89509 C 83.147425,87.204255 83.259543,86.332157 85.492049,81.909573 91.376563,70.252422 106.71663,66.246942 117.86956,73.4554 c 2.72732,1.762784 6.1473,5.439124 7.46321,8.022729 2.4633,4.836334 2.5714,5.626093 2.7944,20.416961 l 0.20986,13.91949 h 6.66296 6.66295 l -0.17607,-14.81034 C 141.32188,87.122875 141.24684,85.984346 140.29169,82.85322 135.83616,68.247788 124.32449,58.313236 109.8539,56.585485 92.048167,54.459504 76.152149,65.136881 70.747624,82.85322 69.79245,85.984346 69.71748,87.122875 69.552442,101.00424 l -0.17608,14.81034 h 6.666729 6.666733 z"
                                    id="path847" />
                          </g>
                        </svg></span></span>
        </h2>
        <?php

        echo displayApiForm($response);
        echo goBack();

    } catch (\Lyra\Exceptions\LyraException $e) {
        if(isApiTest()){
            echo $e->getMessage();
        }
    } catch (Exception $e) {
        if(isApiTest()){
            echo $e->getMessage();
        }
    }
}

function displayTokenForm(){
    try {
        $response = Payzen_Subscribers_Plan::createTokenPlan(
            "EUR",
            "MyOrderId",
            wp_get_current_user()->get('user_email')
        );

        ?>
        <h2><?php _e('Create token','payzen-subscribers'); ?></h2>
        <?php

        echo displayApiForm($response);

    } catch (\Lyra\Exceptions\LyraException $e) {
        if(isApiTest()){
            echo $e->getMessage();
        }
    } catch (Exception $e) {
        if(isApiTest()){
            echo $e->getMessage();
        }
    }
}

function redirectionPayzenApiSdd(){

    $vadsParams = getVadsParams(wp_get_current_user(), get_option('paysubs_api_settings'), '256');
    ?>
    <form id="payzenApiSddForm" method="POST" action="https://secure.payzen.eu/vads-payment/">
        <?php
        foreach ($vadsParams as $key => $vadsParam){
            ?>
            <input type="hidden" name="<?php echo $key;?>" value="<?php echo $vadsParams[$key]; ?>" />
            <?php
        }
        ?>
        <input type="submit" name="payer" value="Payer"/>
    </form>
    <script>
        document.getElementById("payzenApiSddForm").submit();
    </script>
    <?php
}

function displayFinalStep($message){
    ?>
    <h2><?php echo $message;?></h2>
    <?php
}

function goBack(): string
{
    return '<div><a class="go_back" href="javascript:window.history.go(-1)">[ '. __('Go back','payzen-subscribers') .' ]</a></div>';
}