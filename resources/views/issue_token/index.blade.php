@extends('layout.call_page')
@section('content')
<!-- BEGIN: Page Main-->
<div id="loader-wrapper">
    <div id="loader"></div>

    <div class="loader-section section-left"></div>
    <div class="loader-section section-right"></div>
    <style>
        /* CSS untuk menyesuaikan ukuran radio button */
        #nik_konfirmasi_tab input[type="radio"] + span::before,
        #nik_konfirmasi_tab input[type="radio"]:checked + span::before {
            width: 2em; /* Sesuaikan ukuran yang diinginkan */
            height: 2em; /* Sesuaikan ukuran yang diinginkan */
            border-radius: 50%; /* Membuat bentuk lingkaran */
        }
        #nik_konfirmasi_tab input[type="radio"] + span::after,
        #nik_konfirmasi_tab input[type="radio"]:checked + span::after {
            width: 2em; /* Sesuaikan ukuran yang diinginkan */
            height: 2em; /* Sesuaikan ukuran yang diinginkan */
            border-radius: 50%; /* Membuat bentuk lingkaran */
        }
    </style>
</div>
<div id="main" class="noprint" style="padding: 15px 15px 0px;">
    <div class="wrapper">
        <section class="content-wrapper no-print">
            <div class="container no-print">
                <div class="row">
                    <div class="col s12"> 
                        <?php if(!empty($operationalTime)) {?>
                            <div class="card" style="background:#f9f9f9;box-shadow:none" id="service-btn-container">
                                <span class="card-title" style="line-height:1;font-size:22px"> {{__('messages.issue_token.click one service to issue token')}}</span>
                                <br>
                                <span style="font-weight: bold;font-size:25px;color: #a31035">
                                    #Jam Buka Layanan Pukul : <?= $time['on_time'] ?> - <?= $time['off_time'] ?>
                                </span>
                                <div class="divider" style="margin:10px 0 10px 0;"></div>
                                <center>
                                <?php foreach($services as $service): ?>
                                <span class="btn btn-large btn-queue waves-effect waves-light mb-1" id="service_id_24" style="background: #009688" onclick="queueDept({{ json_encode($service) }})">
                                    <?=$service['name']?>
                                    <span class="btn btn-danger btn-xs" readonly style="background: #a31035"><?=$service['remaining_limit']?></span>
                                </span>
                                <?php endforeach ?>
                                </center>
                            </div>
                        <?php }else{ ?>
                            <center>
                                <span style="font-weight: bold;font-size:20px;color: #a31035">
                                    #Jam Buka Layanan Pukul : <?= $time['on_time'] ?> - <?= $time['off_time'] ?>
                                </span><br>
                                <span class="btn btn-large btn-queue waves-effect waves-light mb-1" style="background: #a31035">
                                    Maaf, waktu operasional layanan antrian telah berakhir. Silakan kembali ketika jam buka.
                                </span>
                            </center>
                        <?php } ?>
                    </div>
                    <form action="{{route('create-token')}}" method="post" id="my-form-two" style="display: none;">
                        {{csrf_field()}}


                    </form>
                </div>
            </div>
        </section>
    </div>
    <!-- Modal Structure -->
    <div id="modal1" class="modal modal-fixed-footer" style="max-height: 50%; width:80%">
        <form id="details_form">
            <div class="modal-content" style="padding-bottom:0">
                <div id="inline-form">
                    <div class="card-content">
                        <div class="row">
                            <div class="input-field col s4" id="name_tab">
                                <input id="name" name="name" type="text" value="" data-error=".name">
                                <label for="name">{{__('messages.settings.name')}}</label>
                                <div class="name">

                                </div>
                            </div>
                            <div class="input-field col s4" id="phone_tab">
                                <input id="phone" name="phone" type="text" value="" data-error=".phone">
                                <label for="phone">{{__('messages.settings.phone')}}</label>
                                <div class="phone">

                                </div>
                            </div>
                            <div class="input-field col s4" id="email_tab">
                                <input id="email" name="email" type="email" value="" data-error=".email">
                                <label for="email">{{__('messages.settings.email')}}</label>
                                <div class="email">

                                </div>
                            </div>
                            <div class="input-field col s4" id="nik_konfirmasi_tab">
                                <p>
                                    <label for="nik_konfirmasi">Sudah Mempunyai KTP?</label>
                                </p>
                                <p>
                                    <label>
                                        <input name="nik_konfirmasi" type="radio" id="nik_konfirmasi" value="1" data-error=".nik_konfirmasi" />
                                        <span>Ya</span>
                                    </label>
                                </p>
                                <p>
                                    <label>
                                        <input name="nik_konfirmasi" type="radio" id="nik_konfirmasi" value="0" data-error=".nik_konfirmasi" />
                                        <span>Tidak</span>
                                    </label>
                                </p>
                                <div class="nik_konfirmasi"></div>
                            </div>
                            <div class="input-field col s4" id="nik_tab">
                                <input id="nik" name="nik" type="number" value="" data-error=".nik">
                                <label for="nik">NIK</label>
                                <div class="nik"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="modal_button" type="submit" class="modal-action waves-effect waves-green btn-flat" style="background: #009688; color:#fff" onclick="issueToken()">{{__('messages.common.submit')}}</button>
            </div>
            <form>
    </div>
</div>
@endsection
<div id="printarea" class="printarea" style="text-align:center;margin-top: 20px; display:none">
</div>
@section('js')
<script>
    $(document).ready(function() {
        $('body').addClass('loaded');
        $('.modal').modal();

        $('#nik_tab').hide();
        $('input[name="nik_konfirmasi"]').change(function () {
            if ($(this).val() === '1') {
                $('#nik_tab').show();
                $('#nik').prop('required', true);
            } else if($(this).val() === '0') {
                $('#nik_tab').hide();
                $('#nik').prop('required', false);
            }
        });
    })
    var service;

    function queueDept(value) {
        console.log(value);
        if (value.ask_email == 1 || value.ask_name == 1 || value.ask_phone == 1 || value.ask_nik == 1) {
            if (value.ask_email == 1) $('#email_tab').show();
            else $('#email_tab').hide();
            if (value.ask_name == 1) $('#name_tab').show();
            else $('#name_tab').hide();
            if (value.ask_phone == 1) $('#phone_tab').show();
            else $('#phone_tab').hide()
            if (value.ask_nik == 1) $('#nik_konfirmasi_tab').show();
            else $('#nik_konfirmasi_tab').hide()
            service = value;
            $('#modal_button').removeAttr('disabled');
            $('#modal1').modal('open');
        } else {
            $('body').removeClass('loaded');
            let data = {
                service_id: value.id,
                with_details: false
            }
            createToken(data);
        }
    }

    function issueToken() {
        $('#details_form').validate({
            rules: {
                name: {
                    required: function(element) {
                        return service.name_required == "1";
                    },
                },
                email: {
                    required: function(element) {
                        return service.email_required == "1";
                    },
                    email: true
                },
                phone: {
                    required: function(element) {
                        return service.phone_required == "1";
                    },
                    number: true
                },
            },
            errorElement: 'div',
            errorPlacement: function(error, element) {
                var placement = $(element).data('error');
                if (placement) {
                    $(placement).append(error)
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function(form) {
                $('#modal_button').attr('disabled', 'disabled');
                $('body').removeClass('loaded');
                var phone = $('#phone').val();

                if (phone.startsWith('0')) {
                    phone = '62' + phone.substr(1);
                }

                let data = {
                    service_id: service.id,
                    name: $('#name').val(),
                    email: $('#email').val(),
                    phone: phone,
                    nik: $('#nik').val(),
                    with_details: true
                }
                createToken(data);
            }
        });
    }

    function createToken(data) {
        $.ajax({
            type: "POST",
            url: "{{route('create-token')}}",
            data: data,
            cache: false,
            success: function(response) {
                if (response.status_code == 200) {
                    $('#modal1').modal('close');
                    $('#phone').val(null);
                    $('#email').val(null);
                    $('#name').val(null);
                    $('#nik').val(null);
                    const createdDate = new Date(response.queue.created_at);
                    const formattedDate = createdDate.toLocaleString();
                    let html = `
                        <div style="text-align: center;">
                            <p style="font-size: 15px; font-weight: bold; margin-left:-15px; margin-top:-20px;">` + response.settings.name + ` ` + response.settings.location + `
                            </p>
                            <p style="font-size: 20px; margin-top:-15px;">` + response.queue.service.name + `</p>
                            <h3 style="font-size: 35px; margin-bottom: 5px; font-weight: bold; margin-top:-12px; margin-bottom:16px;">` + response.queue.letter + ` - ` + response.queue.number + `</h3>
                            <div style="margin-top:-20px; margin-bottom:15px;" align="center">
                            </div>
                            <br>
                            <p style="font-size: 15px; margin-top:-12px;">Antrian Menunggu : ` + response.customer_waiting + `  
                            <br>
                            </p>
                            <p style="font-size: 18px; margin-top: -16px;margin-bottom: 27px;">${formattedDate}</p>
                            <br>
                            <p style="font-size: 25px; margin-top: -16px;margin-bottom: 27px;"> </p>
                            ${response.queue.nik ? `<p style="font-size: 20px; margin-top:-12px;">NIK : ` + response.queue.nik + ` </p>` : ''}
                            <p style="text-align:left !important;font-size:18px;"></p>
                            <p style="text-align:right !important; margin-top:-23px;font-size:18px;"></p>
                        </div>`;
                    $('#printarea').html(html);
                    $('body').addClass('loaded');
                        window.print();
                    // if (response.status_code == 200) {
                    //     alert('Silahkan ambil No. Antrian anda!');
                    // } else {
                    //     alert("terjadi Kesalahan pada proses printing!");
                    // }
                        window.location.reload();
                    // $.ajax({
                    //     url : "{{route('print-token')}}",
                    //     type: "POST",
                    //     data: {
                    //         "name": response.settings.name,
                    //         "location": response.settings.location,
                    //         "service_name": response.queue.service.name,
                    //         "que_letter": response.queue.letter,
                    //         "que_number": response.queue.number,
                    //         "que_date": response.queue.formated_date,
                    //         "customer_waiting": response.customer_waiting
                    //     },
                    //     cache: false,
                    //     //success: function(data, textStatus, jqXHR)
                    //     success: function(response) {
                    //         if (response.status_code == 200) {
                    //             alert('Silahkan ambil No. Antrian anda!');
                    //         } else {
                    //             alert("terjadi Kesalahan pada proses printing!");
                    //         }
                    //         window.location.reload();
                    //     }
                    // });
                } else if (response.status_code == 422 && response.errors && (response.errors['name'] || response.errors['email'] || response.errors['phone'])) {
                    $('#modal_button').removeAttr('disabled');
                    if (response.errors['name'] && response.errors['name'][0]) {
                        $('.name').html('<span class="text-danger errbk">' + response.errors['name'][0] + '</span>')
                    }
                    if (response.errors['email'] && response.errors['email'][0]) {
                        $('.email').html('<span class="text-danger errbk">' + response.errors['email'][0] + '</span>')
                    }
                    if (response.errors['phone'] && response.errors['phone'][0]) {
                        $('.phone').html('<span class="text-danger errbk">' + response.errors['phone'][0] + '</span>')
                    }
                    $('body').addClass('loaded');
                    M.toast({
                        html: 'Antrian Sudah Penuh !',
                        classes: "toast-error"
                    });
                } else {
                    $('#modal1').modal('close');
                    $('#phone').val(null);
                    $('#email').val(null);
                    $('#name').val(null);
                    $('#nik').val(null);
                    $('body').addClass('loaded');
                    M.toast({
                        html: response.errors.limit[0],
                        classes: "toast-error"
                    });
                }
            },
            error: function() {
                $('body').addClass('loaded');
                $('#modal1').modal('close');
                M.toast({
                    html: response.errors.limit[0],
                    classes: "toast-error"
                });
            }
        });
    }

    // Fungsi untuk memperbarui halaman
    function refreshPage() {
        location.reload();
    }

    // Fungsi untuk mengatur waktu refresh berdasarkan variabel PHP $time['on_time']
    function setRefreshTime(onTime) {
        // Parsing nilai onTime untuk mendapatkan jam, menit, dan detik
        var onTimeArray = onTime.split(':');
        var refreshHour = parseInt(onTimeArray[0]);
        var refreshMinute = parseInt(onTimeArray[1]);
        var refreshSecond = parseInt(onTimeArray[2]);

        // Mengambil waktu saat ini
        var currentTime = new Date();

        // Mengatur waktu untuk refresh
        var refreshTime = new Date(currentTime.getFullYear(), currentTime.getMonth(), currentTime.getDate(), refreshHour, refreshMinute, refreshSecond);

        // Menghitung selisih waktu antara waktu saat ini dan waktu refresh
        var timeDiff = refreshTime.getTime() - currentTime.getTime();

        // Jika selisih waktu negatif, artinya waktu refresh telah berlalu untuk hari ini, maka tambahkan 1 hari
        if (timeDiff < 0) {
            refreshTime.setDate(refreshTime.getDate() + 1);
            timeDiff = refreshTime.getTime() - currentTime.getTime();
        }

        // Set interval untuk memanggil fungsi refreshPage setelah waktu tertentu (timeDiff)
        setTimeout(refreshPage, timeDiff);
    }

    // Memanggil fungsi setRefreshTime saat halaman dimuat dengan parameter $time['on_time']
    setRefreshTime("<?php echo $time['on_time']; ?>");
    setRefreshTime("<?php echo $time['off_time']; ?>");

</script>
@endsection()