@extends('errors.layout')
@php
    $recoveryTarget = request()->is('admin*')
        ? route('admin.dashboard', [], false)
        : (request()->is('checkout*') ? route('checkout.index', [], false) : route('home', [], false));
@endphp
@section('title', 'نشست منقضی شد | فرزین')
@section('code', '419')
@section('heading', 'نشست شما منقضی شده است')
@section('message', 'برای حفظ امنیت، نشست قبلی پایان یافته است. دوباره وارد شوید؛ پس از ورود به مسیر امن بازمی‌گردید.')
@section('recovery_action_url', route('login', ['intended' => $recoveryTarget]))
@section('recovery_action_label', 'ورود دوباره و ادامه')
