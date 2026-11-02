@extends('errors.layout')
@php
    $adminPath = trim((string) config('farzin.admin_path', 'admin'), '/') ?: 'admin';
    $isAdminRequest = request()->is($adminPath, $adminPath . '/*');
    $recoveryTarget = request()->is('checkout*') ? route('checkout.index', [], false) : route('home', [], false);
@endphp
@section('title', 'نشست منقضی شد | فرزین')
@section('code', '419')
@section('heading', 'نشست شما منقضی شده است')
@section('message', 'برای حفظ امنیت، نشست قبلی پایان یافته است. دوباره وارد شوید؛ پس از ورود به مسیر امن بازمی‌گردید.')
@section('recovery_action_url', $isAdminRequest ? route('admin.login') : route('login', ['intended' => $recoveryTarget]))
@section('recovery_action_label', $isAdminRequest ? 'ورود دوباره به مدیریت' : 'ورود دوباره و ادامه')
