@extends('layout.call_page')
@section('content')
<!-- BEGIN: Page Main-->
<div id="loader-wrapper">
    <div id="loader"></div>

    <div class="loader-section section-left"></div>
    <div class="loader-section section-right"></div>

</div>
<div id="main" class="no-print" style="padding: 15px 15px 0px;">

    <div class="wrapper" style=" min-height: 557px;" id="display-page">
        <section class="content-wrapper no-print">
            <div id="callarea" class="row" style="line-height:2;display:flex; flex-direction:row-reverse">
                <div class="col m4">
                    <div class="card-panel center-align" style="margin-bottom:0; height:40vh; display:flex; flex-direction:row; justify-content:left; align-items:left; font-size: 20px;">
                        <div>
                            <video style="width: 100%; height: 100%;" controls autoplay muted loop>
                                <source src="{{asset('public/uploads/video_display.mp4')}}" type="video/mp4">
                            </video>
                        </div>
                    </div>
                </div>
                <div class="col m8">
                    <div class="card-panel center-align" style="margin-bottom:0;height:40vh;display:flex;flex-direction:row;justify-content:center;align-items:center;font-size: 20px;">
                        <div>
                            <div class="bolder-color" style="font-size:20px; margin:0px">{{__('messages.display.token number')}}</div>
                            <div class="bolder-color" style="font-size:15px; line-height:1.4">@{{tokens[0]?.service.name }}</div>
                            <span v-if="tokens[0]" style="font-size:70px;color:red;font-weight:bold;line-height:1.2">@{{tokens[0]?.token_letter}}-@{{tokens[0]?.token_number}}</span>
                            <span v-if="!tokens[0]" style="font-size:70px;color:red;font-weight:bold;line-height:1.2">{{__('messages.display.nil')}}</span>
                            <div v-if="tokens[0]?.call_status_id == {{CallStatuses::SERVED}}" style="font-size:20px; color:#009688">{{__('messages.display.served')}}</div>
                            <div v-if="tokens[0]?.call_status_id == {{CallStatuses::NOSHOW}}" style="font-size:20px; color:red">{{__('messages.display.noshow')}}</div>
                            <div v-if="tokens[0] && tokens[0]?.call_status_id == null" style="font-size:20px; color:orange; font-weight: bold">{{__('messages.display.serving')}}</div>
                            <div v-if="!tokens[0]" style="font-size:20px; color:orange; font-weight: bold">{{__('messages.display.nil')}}</div>
                            <div class="bolder-color" style="font-size:20px; line-height:1.4">{{__('messages.display.please proceed to')}}</div>
                            <div v-if="tokens[0]" id="counter0" style="font-size:35px; color:red;line-height:1.5">@{{tokens[0]?.counter.name}}</div>
                            <div v-if="!tokens[0]" style="font-size:35px; color:red;line-height:1.5">{{__('messages.display.nil')}}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="callarea" class="row" style="line-height:1;display:flex; flex-direction:row-reverse">
                <div class="col m12">
                    <div class="card-panel center-align p-0" style="margin-bottom:0; height:30vh; font-size: 20px;" id="side-token-display">
                        <div class="row">
                            <div class="row d-flex justify-content-center">
                                <div v-for="(token, index) in tokens.slice(1)" :key="index" class="col m3">
                                    <br><br>
                                    <small v-if="token" class="bolder-color" style="font-size:20px;font-weight:bold;color:red">@{{ token.service.name }}</small><br>
                                    <span v-if="token" class="bolder-color" style="font-size:45px;font-weight:bold;line-height:1.2">@{{ token.token_letter }}-@{{ token.token_number }}</span>
                                    <span v-else class="bolder-color" style="font-size:45px;font-weight:bold;line-height:1.2">{{ __('messages.display.nil') }}</span><br>
                                    <small v-if="token" class="bolder-color" :id="'counter' + index" style="font-size:25px;font-weight:bold;">@{{ token.counter.name }}</small>
                                    <small v-else class="bolder-color" :id="'counter' + index" style="font-size:25px;font-weight:bold;">{{ __('messages.display.nil') }}</small><br>
                                    <small v-if="token && token.call_status_id == {{ CallStatuses::SERVED }}" style="font-size:20px;color:#009688;font-weight:bold;">{{ __('messages.display.served') }}</small>
                                    <small v-if="token && token.call_status_id == {{ CallStatuses::NOSHOW }}" style="font-size:20px;font-weight:bold;color:red">{{ __('messages.display.noshow') }}</small>
                                    <small v-if="token && token.call_status_id == null" style="font-size:20px;color:orange;font-weight:bold;">{{ __('messages.display.serving') }}</small>
                                    <small v-if="!token" style="font-size:20px;">{{__('messages.display.nil')}}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-bottom:0; margin-top: 15px;">
                <marquee><span style="font-size:{{$settings->display_font_size}}px;color:{{$settings->display_font_color}}">{{$settings->display_notification ? $settings->display_notification : 'Hello' }}<span></span></span></marquee>
            </div>
            <audio id="called_sound">
                <source src="{{asset('app-assets/audio/sound.mp3')}}" type="audio/mpeg">
            </audio>
            <audio id="break_sound">
                <source src="storage/app/public/{{$time['sound']}}" type="audio/mpeg">
            </audio>
        </section>
    </div>
</div>
@endsection
@section('b-js')
<script>
    function playSound() {
        var audio = document.getElementById("called_sound");
        audio.play();
    }

    function playBreak() {
        var audio = document.getElementById("break_sound");
        audio.play();
    }

    function refreshPage() {
        location.reload();
    }

    function checkTimeRange() {
        var onTime = "<?php echo $time['break_time_start']; ?>";
        var offTime = "<?php echo $time['break_time_finish']; ?>";

        var currentTime = new Date();
        var currentHour = currentTime.getHours();
        var currentMinute = currentTime.getMinutes();

        var onTimeArray = onTime.split(':');
        var onHour = parseInt(onTimeArray[0]);
        var onMinute = parseInt(onTimeArray[1]);

        var offTimeArray = offTime.split(':');
        var offHour = parseInt(offTimeArray[0]);
        var offMinute = parseInt(offTimeArray[1]);

        var currentTotalMinutes = currentHour * 60 + currentMinute;
        var onTotalMinutes = onHour * 60 + onMinute;
        var offTotalMinutes = offHour * 60 + offMinute;

        if (currentTotalMinutes >= onTotalMinutes && currentTotalMinutes <= offTotalMinutes) {
            setInterval(playBreak, 30000);
        }
    }

    checkTimeRange();
</script>

<script>
    window.JLToken = {
        // get_tokens_for_display_url: "{{ asset($file) }}",
        get_tokens_for_display_url: "{{ route('get-tokens-for-display') }}",
        get_initial_tokens: "{{ route('get-tokens-for-display') }}",
        date_for_display: "{{$date}}",
        voice_type: "{{$settings->language->display}}",
        voice_content_one: "{{$settings->language->token_translation}}",
        voice_content_two: "{{$settings->language->please_proceed_to_translation}}",
        date_for_display: "{{$date}}",
        audioEl: document.getElementById('called_sound'),
    }
</script>
@endsection 