<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatbotController extends Controller
{
    /**
     * Handle incoming chat message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        // ۱. دریافت پیام کاربر از ریکوست
        $userMessage = strtolower(trim($request->input('message', '')));

        // ۲. پیدا کردن پاسخ مناسب
        $responseMessage = $this->getBotResponse($userMessage);

        // ۳. بازگرداندن پاسخ در قالب JSON
        return response()->json(['reply' => $responseMessage]);
    }

    /**
     * Determines the bot's response based on the user's message.
     *
     * @param  string  $message
     * @return string
     */
    private function getBotResponse(string $message): string
    {
        // در اینجا منطق اصلی چت‌بات قرار می‌گیرد
        // ما با استفاده از کلمات کلیدی، پاسخ مناسب را پیدا می‌کنیم

        if (str_contains($message, 'سلام') || str_contains($message, 'وقت بخیر')) {
            return 'سلام! خوش آمدید. چطور می‌توانم کمکتان کنم؟ می‌توانید در مورد خدمات، قیمت‌ها یا نحوه رزرو سوال بپرسید.';
        }

        if (str_contains($message, 'خدمات') || str_contains($message, 'ماساژ')) {
            return 'ما خدمات متنوعی از جمله ماساژ سوئدی، سنگ داغ، ورزشی و تایلندی را ارائه می‌دهیم. برای مشاهده لیست کامل، لطفاً به صفحه خدمات مراجعه کنید.';
        }

        if (str_contains($message, 'قیمت') || str_contains($message, 'هزینه')) {
            return 'قیمت‌ها بسته به نوع و مدت زمان ماساژ متفاوت است. برای اطلاع دقیق از قیمت‌ها می‌توانید به صفحه خدمات مراجعه کرده و سرویس مورد نظر خود را انتخاب کنید.';
        }

        if (str_contains($message, 'رزرو') || str_contains($message, 'نوبت')) {
            return 'برای رزرو نوبت می‌توانید از صفحه "رزرو نوبت" در منوی اصلی سایت اقدام کنید. در آنجا می‌توانید روز و ساعت دلخواه خود را انتخاب نمایید.';
        }

        if (str_contains($message, 'آدرس') || str_contains($message, 'کجایید')) {
            return 'آدرس دقیق ما در صفحه "تماس با ما" ذکر شده است. همچنین می‌توانید از نقشه موجود در همان صفحه برای مسیریابی استفاده کنید.';
        }

        if (str_contains($message, 'پشتیبانی') || str_contains($message, 'اپراتور')) {
            return 'در حال حاضر مشغول صحبت با ربات پشتیبانی هستید. اگر سوال شما حل نشد، لطفاً با شماره‌های موجود در صفحه "تماس با ما" تماس بگیرید.';
        }

        // پاسخ پیش‌فرض در صورتی که هیچ کلمه کلیدی پیدا نشود
        return 'متوجه سوال شما نشدم. لطفاً سوال خود را به شکل دیگری بپرسید یا از کلمات کلیدی مانند "خدمات"، "قیمت" یا "رزرو" استفاده کنید.';
    }
}
