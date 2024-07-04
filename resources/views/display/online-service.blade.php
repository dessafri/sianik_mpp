@extends('layout.call_page')
@section('content')
<!-- BEGIN: Page Main-->
<div id="loader-wrapper">
    <div id="loader"></div>

    <div class="loader-section section-left"></div>
    <div class="loader-section section-right"></div>
    <style>
        ul#menu {
            padding: 0;
        }

        ul#menu li {
            display: inline;
        }

        ul#menu li a {
            background-color: green;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px 4px 0 0;
        }

        ul#menu li a:hover {
            background-color: red;
        }
    </style>
</div>
<div id="main" class="no-print" style="padding: 15px 15px 0px;">

    <div class="wrapper" style="min-height: 557px;" id="display-page">
        <section class="content-wrapper no-print">
            <div id="callarea" class="row" style="line-height: 2; display: flex; flex-direction: row-reverse">
                <div class="col m12">
                    <div class="card-panel center-align" style="margin-bottom: 0; height: 40vh; display: flex; flex-direction: row; justify-content: center; align-items: center; font-size: 20px;">
                        <div>
                            <div class="bolder-color" style="font-size: 70px; margin: 0px">{{__('messages.display.token number')}}</div>
                            <div class="bolder-color" style="font-size: 65px; line-height: 1.4">@{{tokens[0]?.service.name }}</div>
                            <span v-if="tokens[0]" style="font-size: 120px; color: red; font-weight: bold; line-height: 1.2">@{{tokens[0]?.token_letter}}-@{{tokens[0]?.token_number}}</span>
                            <span v-if="!tokens[0]" style="font-size: 120px; color: red; font-weight: bold; line-height: 1.2">{{__('messages.display.nil')}}</span>
                            <div v-if="tokens[0]?.call_status_id == {{CallStatuses::SERVED}}" style="font-size: 70px; color: #009688">{{__('messages.display.served')}}</div>
                            <div v-if="tokens[0]?.call_status_id == {{CallStatuses::NOSHOW}}" style="font-size: 70px; color: red">{{__('messages.display.noshow')}}</div>
                            <div v-if="tokens[0] && tokens[0]?.call_status_id == null" style="font-size: 70px; color: orange; font-weight: bold">{{__('messages.display.serving')}}</div>
                            <div v-if="!tokens[0]" style="font-size: 70px; color: orange; font-weight: bold">{{__('messages.display.nil')}}</div>
                            <div class="bolder-color" style="font-size: 70px; line-height: 1.4">{{__('messages.display.please proceed to')}}</div>
                            <div v-if="tokens[0]" id="counter0" style="font-size: 55px; color: red; line-height: 1.5">@{{tokens[0]?.counter.name}}</div>
                            <div v-if="!tokens[0]" style="font-size: 55px; color: red; line-height: 1.5">{{__('messages.display.nil')}}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="callarea" class="row" style="line-height: 1; display: flex; flex-direction: row-reverse">
                <div class="col m12">
                    <div class="card-panel center-align p-0" style="margin-bottom: 0; height: auto; font-size: 20px;" id="side-token-display">
                        <center><h3>Rekam Pemanggilan</h3></center>
                        <div class="justify-content-center">
                            <table>
    @foreach($services as $service)
        @php
            $serviceName = $service->name;
        @endphp
        <tr>
            <td>
                <table>
                    <tr>
                        <td>
                            <small class="bolder-color" style="font-size:45px;font-weight:bold;color:red">{{ $serviceName }}</small><br>
                            <template v-for="(token, index) in tokens" :key="index">
                                <template v-if="token.service.name === '{{ $serviceName }}'">
                                    <div>
                                        <span class="bolder-color" style="font-size:40px;font-weight:bold;line-height:1.2">@{{ token.token_letter }}-@{{ token.token_number }} - </span>
                                        <small class="bolder-color" :id="'counter' + index" style="font-size:35px;font-weight:bold;">@{{ token.counter.name }} - </small>
                                        <small v-if="token && token.call_status_id == {{ CallStatuses::SERVED }}" style="font-size:30px;color:#009688;font-weight:bold;">[{{ __('messages.display.served') }}]</small>
                                        <small v-if="token && token.call_status_id == {{ CallStatuses::NOSHOW }}" style="font-size:30px;font-weight:bold;color:red">[{{ __('messages.display.noshow') }}]</small>
                                        <small v-if="token && token.call_status_id == null" style="font-size:30px;color:orange;font-weight:bold;">[{{ __('messages.display.serving') }}]</small>
                                    </div>
                                </template>
                            </template>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    @endforeach
</table>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-bottom: 0; margin-top: 15px;">
                <marquee><span style="font-size: 50px; color: {{$settings->display_font_color}}">{{$settings->display_notification ? $settings->display_notification : 'Hello'}}<span></span></span></marquee>
            </div>
            <audio id="called_sound" muted>
                <source src="{{asset('app-assets/audio/sound.mp3')}}" type="audio/mpeg">
            </audio>
        </section>
    </div>
</div>
@endsection
@section('b-js')
<script>
    window.JLToken = {
        get_tokens_for_display_url: "{{ route('get-tokens-for-display-service') }}",
        get_initial_tokens: "{{ route('get-tokens-for-display-service') }}",
        date_for_display: "{{$date}}",
        voice_type: "{{$settings->language->display}}",
        voice_content_one: "{{$settings->language->token_translation}}",
        voice_content_two: "{{$settings->language->please_proceed_to_translation}}",
        date_for_display: "{{$date}}",
        audioEl: document.getElementById('called_sound'),
    }
</script>
@endsection