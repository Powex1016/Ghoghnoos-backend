<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str; // این خط برای کار با رشته‌ها اضافه شده

class SearchController extends Controller
{
    /**
     * Handle a search request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        // 1. لیست کامل تمام موارد قابل جستجو در سایت
        // ما اینجا یک آرایه کامل از هر چیزی که می‌خواهیم جستجو شود تعریف می‌کنیم.
        // هر آیتم شامل نام، آدرس صفحه و نوع آن است.
        $searchableItems = [
            // خدمات اصلی
            ['name' => 'ماساژ سوئدی (Swedish)', 'url' => './services.html', 'type' => 'سرویس'],
            ['name' => 'ماساژ بافت عمیق (Deep Tissue)', 'url' => './services.html', 'type' => 'سرویس'],
            ['name' => 'ماساژ سنگ داغ (Hot Stone)', 'url' => './services.html', 'type' => 'سرویس'],
            ['name' => 'ماساژ ورزشی (Sports Massage)', 'url' => './services.html', 'type' => 'سرویس'],
            ['name' => 'ماساژ تایلندی (Thai Massage)', 'url' => './services.html', 'type' => 'سرویس'],
            ['name' => 'ماساژ شیاتسو (Shiatsu)', 'url' => './services.html', 'type' => 'سرویس'],
            ['name' => 'آروماتراپی (Aromatherapy)', 'url' => './services.html', 'type' => 'سرویس'],
            ['name' => 'رفلکسولوژی (Reflexology)', 'url' => './services.html', 'type' => 'سرویس'],
            ['name' => 'ماساژ سر و صورت', 'url' => './services.html', 'type' => 'سرویس'],
            ['name' => 'ماساژ فرم‌دهی (Slimming)', 'url' => './services.html', 'type' => 'سرویس'],
            // پکیج‌ها و پیشنهادات
            ['name' => 'پکیج طلایی', 'url' => './offers.html', 'type' => 'پکیج'],
            ['name' => 'پکیج نقره‌ای', 'url' => './offers.html', 'type' => 'پکیج'],
            ['name' => 'پکیج برنزی', 'url' => './offers.html', 'type' => 'پکیج'],
            ['name' => 'کارت هدیه ققنوس', 'url' => './offers.html', 'type' => 'پیشنهاد'],
            ['name' => 'باشگاه مشتریان ققنوس', 'url' => './offers.html', 'type' => 'پیشنهاد'],
            // صفحات اصلی
            ['name' => 'صفحه اصلی', 'url' => './index.html', 'type' => 'صفحه'],
            ['name' => 'درباره ما', 'url' => './about.html', 'type' => 'صفحه'],
            ['name' => 'تماس با ما', 'url' => './contact.html', 'type' => 'صفحه'],
        ];

        // 2. فیلتر کردن لیست بر اساس جستجوی کاربر
        $results = [];
        foreach ($searchableItems as $item) {
            // Str::contains بررسی می‌کند که آیا عبارت جستجو شده (query) در نام آیتم (item['name']) وجود دارد یا خیر.
            // این تابع به حروف کوچک و بزرگ حساس نیست.
            if (Str::contains(strtolower($item['name']), strtolower($query))) {
                $results[] = $item; // اگر پیدا شد، آن را به لیست نتایج اضافه کن
            }
        }

        // 3. بازگرداندن نتایج در قالب JSON
        // ما اینجا کلید 'data' را نگه می‌داریم تا با کدهای قبلی شما در فرانت‌اند سازگار باشد.
        return response()->json(['data' => $results]);
    }
}
