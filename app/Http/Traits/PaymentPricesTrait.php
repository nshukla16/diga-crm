<?php
namespace App\Http\Traits;

use Log;
use Exception;
use App\Module;
use App\Setting;
use Carbon\Carbon;
use App\UserPayment;

  /**
   * PaymentPricesTrait - duplicates price calculation in js while buying subscription
   * don't forget to change if changing payments/index.vue
   * 
   */
trait PaymentPricesTrait 
{
    public function price_for_users($number_of_workers, $number_of_months)
    {
        $price_per_user_setting = Setting::where('key', '=', 'price_per_user')->first();

        return $number_of_workers * $number_of_months * $price_per_user_setting->value;
    }

    public function price_for_modules($selected_modules, $number_of_months)
    {
        $price = 0;

        foreach($selected_modules as $sm)
        {
            $price += $sm['price'];
        }

        return round($price * $number_of_months);
    }

    public function discount_percent($number_of_months)
    {
        switch($number_of_months){
            default:
                return 0;
                break;
            case 3:
                return 3;
                break;
            case 6: 
                return 6;
                break;
            case 12:
                return 10;
                break;
        }
    }

    public function discount($selected_modules, $number_of_workers, $number_of_months)
    {
        $sum = $this->price_for_modules($selected_modules, $number_of_months) + $this->price_for_users($number_of_workers, $number_of_months);
        $discount_amount = $sum * $this->discount_percent($number_of_months);
        if ($discount_amount > 0)
        {
            return round($discount_amount / 100);
        }
        return 0;
    }

    public function previous_subscriptions_discount()
    {
        $now = Carbon::now()->toDateTimeString();
        $active_modules = Module::whereNotNull('current_subscription_date_end')->where('current_subscription_date_end', '>', $now)->get();
        $sum = 0.0;

        if ($active_modules->count() == 0)
        {
            return 0;
        }

        $last_successfull_payment = UserPayment::
            where('status', '=', 'submitted_for_settlement')->
            orWhere('status', '=', 'balance')->
            orWhere('status', '=', 'approved')->
            get()->
            sortByDesc('created_at')->first();

        if ($last_successfull_payment == null)
        {
            return 0;
        }
        if ($last_successfull_payment->sum <= 0)
        {
            return 0;
        }

        $any_module = $active_modules->first();

        $days_number = Carbon::parse($any_module->current_subscription_date_end)->diffInDays(Carbon::parse($any_module->current_subscription_date_start));

        $price_per_day = $last_successfull_payment->sum / $days_number;

        $left_price = $price_per_day * Carbon::parse($any_module->current_subscription_date_end)->diffInDays($now);

        return round($left_price);
    }

    public function total_sum($selected_modules, $number_of_workers, $number_of_months)
    {
        $setting_balance =  Setting::where('key', '=', 'company_balance')->first();

        return round($this->price_for_modules($selected_modules, $number_of_months) + 
            $this->price_for_users($number_of_workers, $number_of_months) - 
            $this->discount($selected_modules, $number_of_workers, $number_of_months) - $setting_balance->value - 
            $this->previous_subscriptions_discount()
        );
    }

    public function to_balance($selected_modules, $number_of_workers, $number_of_months)
    {
        $setting_balance =  Setting::where('key', '=', 'company_balance')->first();

        if ($this->previous_subscriptions_discount() > 0)
            return round($this->previous_subscriptions_discount() - 
                ($this->price_for_modules($selected_modules, $number_of_months) + 
                $this->price_for_users($number_of_workers, $number_of_months) - $this->discount($selected_modules, $number_of_workers, $number_of_months)));
        else
            return round(
                (($this->price_for_modules($selected_modules, $number_of_months) + 
                $this->price_for_users($number_of_workers, $number_of_months) - 
                $this->discount($selected_modules, $number_of_workers, $number_of_months)) - 
                $setting_balance->value) + $setting_balance->value
            ) * (-1);
    }
}
