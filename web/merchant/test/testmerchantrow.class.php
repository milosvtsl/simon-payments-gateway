<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 9/12/2016
 * Time: 12:51 PM
 */
namespace Merchant\Test;

use Merchant\Model\MerchantRow;

class TestMerchantRow extends MerchantRow
{
    public function __construct(Array $params = array()) {
        parent::__construct(array(
                'id' => 27,
                'uid' => '359ae330-e892-43ce-a3f2-3bae893df26381',
                'version' => 1,
                'address1' => 'Fake Address 123',
                'address2' => '#101',
                'agent_chain' => '0j0876',
                'batch_capture_time' => '08:00 PM',
                'batch_capture_time_zone' => 'EST',
                'city' => 'Miami',
                'convenience_fee_flat' => 0,
                'convenience_fee_limit' => 0,
                'convenience_fee_variable_rate' => 3.95,
                'amex_external' => 1096643534,
                'discover_external' => 601105011666431,
                'gateway_id' => 4445018852749,
                'gateway_token' => null,
                'main_contact' => 'Sandra Test',
                'main_email_id' => 'sandra@test.com',
                'merchant_id' => 4445017451724,
                'name' => 'Interamerica Data Florida',
                'notes' => 'Test Notes',
                'open_date' => '2015-03-04 00:00:00',
                'profile_id' => 4445017451721,
                'sale_rep' => 'Referral',
                'short_name' => 'In Da Fl',
                'sic' => 7549,
                'store_id' => 0020,
                'telephone' => '305 9828371',
                'zipcode' => 33147,

                'payout_type' => 'BANK_ACCOUNT',
                'payout_account_name' => 'Fran Lemke',
                'payout_account_type' => 'SAVINGS',
                'payout_account_number' => '123123123',
                'payout_bank_code' => '123123123',

                'url' => 'http://paylogicnetwork.com',
            ) + $params);
    }
}