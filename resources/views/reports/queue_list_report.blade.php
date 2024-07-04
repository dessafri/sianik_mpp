@extends('layout.app')
@section('title','Reports')
@section('report','active')
@section('queue_list_report','active')
@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/data-tables/css/jquery.dataTables.min.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/data-tables/extensions/responsive/css/responsive.dataTables.min.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/data-tables/css/select.dataTables.min.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/pages/data-tables.css')}}">
@endsection
@section('content')
<div id="main">
    <div id="breadcrumbs-wrapper">
        <div class="container">
            <div class="row">
                <div class="col s12 m12 l12 pb-1">
                    <h5 class="breadcrumbs-title col s5"><b>{{__('messages.menu.queue list')}}</b></h5>
                    <ol class="breadcrumbs col s7 right-align">
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col s12 m12 l12">
            <div id="inline-form" class="card card card-default scrollspy">
                <div class="card-content">
                    <form action="{{route('queue_list_report')}}" id="queue_list_report_form" autocomplete="off">
                        <div class="row">
                            <div class="input-field col m5 s4">
                                <input id="starting_date" name="starting_date" type="text" class="datepicker" data-error=".starting_date" value="{{$selected_starting_date}}">
                                <label for="starting_date">{{__('messages.reports.starting date')}}</label>
                                <div class="starting_date">
                                    @if ($errors->has('starting_date'))
                                    <span class="text-danger errbk">{{ $errors->first('starting_date') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="input-field col m5 s4">
                                <input id="ending_date" name="ending_date" type="text" class="datepicker" value="{{$selected_ending_date}}" data-error=".ending_date">
                                <label for="ending_date">{{__('messages.reports.ending date')}}</label>
                                <div class="ending_date">
                                    @if ($errors->has('ending_date'))
                                    <span class="text-danger errbk">{{ $errors->first('ending_date') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="input-field col m2 s2">
                                <div class="input-field col s12">
                                    <button class="btn waves-effect waves-light" id="gobtn" type="submit">
                                        {{__('messages.reports.go')}}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <a href="{{ url('reports/export?starting_date=' . $selected_starting_date . '&ending_date=' . $selected_ending_date) }}" class="btn btn-success" id="exportBtn">{{ __('Export to Excel') }}</a>
                </div>
            </div>
        </div>
    </div>
    @if($reports)
    <div class="col s12">
        <div class="container" style="width: 99%;">
            <div class="section-data-tables">
                <div class="row">
                    <div class="col s12">
                        <div class="card">
                            <div class="card-content">
                                <div class="row">
                                    <div class="col s12">
                                        <div class="table-responsive">
                                            <table id="page-length-option" class="display dataTable">
                                                <thead>
                                                    <tr>
                                                        <th width="10px">#</th>
                                                        <th>{{__('messages.reports.service')}}</th>
                                                        <th>{{__('messages.reports.date')}}</th>
                                                        <th>{{__('messages.reports.token number')}}</th>
                                                        <th>Nama</th>
                                                        <th>NIK</th>
                                                        <th>Telp</th>
                                                        <th>Status</th>
                                                        <th>{{__('messages.reports.called')}}</th>
                                                        <th>{{__('messages.reports.user')}}</th>
                                                        <th>{{__('messages.reports.counter')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($reports as $key=>$report)
                                                    <tr>
                                                        <td>{{ $key+1 }}</td>
                                                        <td>{{$report->service_name}}</td>
                                                        <td>{{\Carbon\Carbon::parse($report->date)->timezone($timezone)->toDateString()}}</td>
                                                        <td>{{$report->token_letter}}-{{$report->token_number}}</td>
                                                        <td>{{$report->name}}</td>
                                                        <td>{{$report->nik}}</td>
                                                        <td>
                                                            {{$report->phone}}<br>
                                                            <?php if ($report->phone) { ?>
                                                                <a href="{{ url('reports/sendMessage?id=' . $report->id) }}" class="btn btn-info">{{ __('Kirim Pesan') }}</a>
                                                            <?php } ?>
                                                        </td>
                                                        <td>{{$report->status_queue}}</td>
                                                        <td>{{$report->called ==1 ? 'Yes' : 'No'}}</td>
                                                        <td>{{$report->user_name ? $report->user_name : 'Nil' }}</td>
                                                        <td>{{$report->counter_name ? $report->counter_name : 'Nil' }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
@section('js')
<script src="{{asset('app-assets/vendors/data-tables/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('app-assets/vendors/data-tables/extensions/responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('app-assets/vendors/data-tables/js/dataTables.select.min.js')}}"></script>
<script src="{{asset('app-assets/vendors/jquery-validation/jquery.validate.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd'
        });

        // Jika kedua tanggal sudah diisi, tampilkan tombol export
        if (startingDate && endingDate) {
            $('#exportBtn').show();
        } else {
            // Jika salah satu atau kedua tanggal kosong, sembunyikan tombol export
            $('#exportBtn').hide();
        }

        // Mendapatkan tanggal hari ini dalam format YYYY-MM-DD
        var today = new Date();
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0'); // Januari dimulai dari 0
        var yyyy = today.getFullYear();
        var currentDate = yyyy + '-' + mm + '-' + dd;

        // Mengisi input starting_date dengan tanggal hari ini
        $('#starting_date').val(currentDate);
        $('#ending_date').val(currentDate);

        $('body').addClass('loaded');

        var starting_date = $('#starting_date').val();
        var ending_date = $('#ending_date').val();

        if (starting_date == "" || ending_date == "") {
            $('#gobtn').attr('disabled', 'disabled');
        } else {
            $('#gobtn').removeAttr('disabled');
        }

    });

    $('#starting_date,#ending_date').change(function(event) {
        let starting_date = $('#starting_date').val();
        let ending_date = $('#ending_date').val();

        if (starting_date == "" || ending_date == "") {
            $('#gobtn').attr('disabled', 'disabled');
        } else {
            $('#gobtn').removeAttr('disabled');
        }
    });

    $('#queue_list_report_form').validate({
        rules: {
            starting_date: {
                required: true,
            },
            ending_date: {
                required: true,
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
        }
    });

    $('#page-length-option').DataTable({
        "responsive": true,
        "searching": true,
        "autoHeight": false,
        "scrollX": true,
        "lengthMenu": [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
    });
</script>
@endsection