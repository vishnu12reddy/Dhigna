<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Setting;

class SettingsTableSeederCustom extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $setting = $this->findSetting("apps.authorize_login_id");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "Authorize.Net Login Id", "value" => "", "details"=> null, "type" => "text", "order" => "47", "group" => "Apps", ])->save();
        }

        $setting = $this->findSetting("apps.authorize_transaction_key");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "Authorize.Net Transaction Key", "value" => "", "details"=> null, "type" => "text", "order" => "48", "group" => "Apps", ])->save();
        }

        $setting = $this->findSetting("apps.authorize_test_mode");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "Authorize.Net Test Mode", "value" => "1", "details" => json_encode([
                "validation" => [
                    "rule" => "in:0,1,on,off"
                ]
            ]), "type" => "checkbox", "order" => "49", "group" => "Apps", ])->save();
        }

        $setting = $this->findSetting("apps.stripe_public_key");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "Stripe Public Key", "value" => "", "details"=> null, "type" => "text", "order" => "50", "group" => "Apps", ])->save();
        }

        $setting = $this->findSetting("apps.stripe_secret_key");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "Stripe Secret Key", "value" => "", "details"=> null, "type" => "text", "order" => "51", "group" => "Apps", ])->save();
        }

        $setting = $this->findSetting("apps.stripe_direct");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "Stripe Direct (Auto-payout to Organizers)", "value" => "0", "details" => json_encode([
                "validation" => [
                    "rule" => "in:0,1,on,off"
                ]
            ]), "type" => "checkbox", "order" => "51", "group" => "Apps", ])->save();
        }

        $setting = $this->findSetting("apps.bitpay_key_name");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "BitPay Key Name", "value" => "", "details"=> null, "type" => "text", "order" => "54", "group" => "Apps", ])->save();
        }

        $setting = $this->findSetting("apps.bitpay_encrypt_code");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "BitPay Encrypt Code", "value" => "", "details"=> null, "type" => "text", "order" => "55", "group" => "Apps", ])->save();
        }

        $setting = $this->findSetting("apps.bitpay_production");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "BitPay Production", "value" => "0", "details" => json_encode([
                "validation" => [
                    "rule" => "in:0,1,on,off"
                ]
            ]), "type" => "checkbox", "order" => "55", "group" => "Apps", ])->save();
        }

        $setting = $this->findSetting("apps.twilio_sid");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "Twilio Sid", "value" => "", "details"=> null, "type" => "text", "order" => "56", "group" => "Apps", ])->save();
        }

        $setting = $this->findSetting("apps.twilio_auth_token");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "Twilio Auth Token", "value" => "", "details"=> null, "type" => "text", "order" => "57", "group" => "Apps", ])->save();
        }

        $setting = $this->findSetting("apps.twilio_number");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "Twilio Number", "value" => "", "details"=> null, "type" => "text", "order" => "58", "group" => "Apps", ])->save();
        }

        $setting = $this->findSetting("apps.paystack_public_key");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "PayStack Public Key", "value" => "", "details"=> null, "type" => "text", "order" => "59", "group" => "Apps", ])->save();
        }

        $setting = $this->findSetting("apps.paystack_secret_key");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "PayStack Secret Key", "value" => "", "details"=> null, "type" => "text", "order" => "60", "group" => "Apps", ])->save();
        }

        $setting = $this->findSetting("apps.paystack_merchant_email");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "PayStack Merchant Email", "value" => "", "details"=> null, "type" => "text", "order" => "61", "group" => "Apps", ])->save();
        }
        
        $setting = $this->findSetting("apps.razorpay_keyid");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "RazorPay Key ID", "value" => "", "details"=> null, "type" => "text", "order" => "62", "group" => "Apps", ])->save();
        }
        $setting = $this->findSetting("apps.razorpay_keysecret");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "RazorPay Key Secret", "value" => "", "details"=> null, "type" => "text", "order" => "63", "group" => "Apps", ])->save();
        }
        
        $setting = $this->findSetting("apps.paytm_production");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "PayTM Live Mode", "value" => "0", "details" => json_encode([
                "validation" => [
                    "rule" => "in:0,1,on,off"
                ]
            ]), "type" => "checkbox", "order" => "64", "group" => "Apps", ])->save();
        }
        $setting = $this->findSetting("apps.paytm_merchant_id");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "PayTM Merchant ID", "value" => "", "details"=> null, "type" => "text", "order" => "65", "group" => "Apps", ])->save();
        }
        $setting = $this->findSetting("apps.paytm_merchant_key");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "PayTM Merchant Key", "value" => "", "details"=> null, "type" => "text", "order" => "66", "group" => "Apps", ])->save();
        }
        $setting = $this->findSetting("apps.paytm_merchant_website");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "PayTM Merchant Website", "value" => "", "details"=> null, "type" => "text", "order" => "67", "group" => "Apps", ])->save();
        }
        $setting = $this->findSetting("apps.paytm_channel");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "PayTM Channel", "value" => "", "details"=> null, "type" => "text", "order" => "68", "group" => "Apps", ])->save();
        }
        $setting = $this->findSetting("apps.paytm_industry_type");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "PayTM Industry Type", "value" => "", "details"=> null, "type" => "text", "order" => "69", "group" => "Apps", ])->save();
        }
        
        $setting = $this->findSetting("regional.timezone_conversion");
        if (!$setting->exists) {
            $setting->fill(["display_name" => "Timezone Conversion", "value" => "1", "details" => json_encode([
                "validation" => [
                    "rule" => "in:0,1,on,off"
                ]
            ]), "type" => "checkbox", "order" => "70", "group" => "Regional", ])->save();
        }
        
    }

    /**
     * [setting description].
     *
     * @param [type] $key [description]
     *
     * @return [type] [description]
     */
    protected function findSetting($key)
    {
        return Setting::firstOrNew(['key' => $key]);
    }
}