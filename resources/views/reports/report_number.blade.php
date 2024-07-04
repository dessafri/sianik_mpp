@extends('layout.app')
@section('title','Report Nomor Antrian')
@section('report','active')
@section('report_number','active')
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
                <div class="col s12 m12 l12">
                    <h5 class="breadcrumbs-title col s5"><b>Report Nomor Antrian</b></h5>
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
                    <form action="{{route('report_number')}}" id="report_number_form" autocomplete="off">
                        <div class="row">
                            <div class="input-field col m3 s3">
                                <input id="starting_date" name="starting_date" type="text" class="datepicker" data-error=".starting_date" value="{{$selected['starting_date']}}">
                                <label for="starting_date">{{__('messages.reports.starting date')}}</label>
                                <div class="starting_date">
                                    @if ($errors->has('starting_date'))
                                    <span class="text-danger errbk">{{ $errors->first('starting_date') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="input-field col m3 s3">
                                <input id="ending_date" name="ending_date" type="text" class="datepicker" value="{{$selected['ending_date']}}" data-error=".ending_date">
                                <label for="ending_date">{{__('messages.reports.ending date')}}</label>
                                <div class="ending_date">
                                    @if ($errors->has('ending_date'))
                                    <span class="text-danger errbk">{{ $errors->first('ending_date') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="input-field col s4">
                                <select name="service_id" id="service_id" data-error=".service_id">
                                    @foreach($services as $service)
                                    <option value="{{$service->id}}" {{ $service->id == $selected['service'] ?'selected':''}}>{{$service->name}}</option>
                                    @endforeach
                                </select>
                                <label>{{__('messages.reports.select service')}}</label>
                                <div class="service_id">
                                    @if ($errors->has('service_id'))
                                    <span class="text-danger errbk">{{ $errors->first('service_id') }}</span>
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
                    <a href="{{ url('reports/exportReportNumber?service_id=' . $selected['service'] . '&starting_date=' . $selected['starting_date']. '&ending_date=' . $selected['ending_date']) }}" class="btn btn-success" id="exportBtn">{{ __('Export to Excel') }}</a>
                </div>
            </div>
        </div>
    </div>
    @if($report_numbers)
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
                                                        <th>Nomor</th>
                                                        <th>Total</th>
                                                        <th>{{__('messages.user_page.action')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($report_numbers as $index => $data)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $data->phone }}
                                                            @if(\App\Models\BlockedNumber::where('phone_number', $data->phone)->exists())
                                                                ❌
                                                            @endif
                                                        </td>
                                                        <td>{{ $data->total }}</td>
                                                        <td>
                                                            <a class="btn-floating btn-action waves-effect waves-light red tooltipped" href="{{ url('reports/add_block_number?phone='. $data->phone)}}" data-position=top data-tooltip="Blokir Nomor"><i class="material-icons">add</i></a>
                                                        </td>
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

    $('#report_number_form').validate({
        rules: {
            starting_date: {
                required: true,
            },
            ending_date: {
                required: true,
            },
            service_id: {
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
        "autoHeight": false,
        "searching": true,
        "scrollX": true,
        "lengthMenu": [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ]
    });
</script>
@endsection