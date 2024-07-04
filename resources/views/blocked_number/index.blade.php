@extends('layout.app')
@section('title','Blokir Nomor')
@section('blocked_number','active')
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
                    <h5 class="breadcrumbs-title col s5"><b>Blokir Nomor</b></h5>
                    <ol class="breadcrumbs col s7 right-align">
                        <a class="btn-floating waves-effect waves-light tooltipped" href="{{route('blocked_number.create')}}" data-position="top" data-tooltip="Tambah Blokir Nomor">
                            <i class="material-icons">add</i>
                        </a>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="col s12">
        <div class="container" style="width: 99%;">
            <div class="section-data-tables">
                <div class="row">
                    <div class="col s12">
                        <div class="card">
                            <div class="card-content">
                                <div class="row">
                                    <div class="col s12">
                                        <form id="block_numbers" method="post" action="{{route('bulk_delete')}}">
                                        {{csrf_field()}}
                                            <table id="page-length-option" class="display dataTable">
                                                <thead>
                                                    <tr>
                                                        <th width="10px">#</th>
                                                        <th>
                                                            <label>
                                                                <input type="checkbox" id="select_all" />
                                                                <span>{{__('messages.user_roles_page.select all')}}</span>
                                                            </label>
                                                        </th>
                                                        <th>Nomor Telepon</th>
                                                        <th>Tanggal Pemblokiran</th>
                                                        <th>{{__('messages.user_page.action')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($blocked_numbers as $key=>$blockedNumber)
                                                    <tr>
                                                        <td>{{$key+1}}</td>
                                                        <td>
                                                            <label>
                                                                <input type="checkbox" class="checkbox" id="blocked_number[]" value="{{ $blockedNumber->id }}" name="blocked_number[]" />
                                                                <span></span>
                                                            </label>    
                                                        </td>
                                                        <td>{{ $blockedNumber->phone_number }}</td>
                                                        <td>{{ $blockedNumber->created_at }}</td>
                                                        <td>
                                                            <a class="btn-floating btn-action waves-effect waves-light orange tooltipped" href="{{route('blocked_number.edit',[$blockedNumber->id])}}" data-position=top data-tooltip="{{__('messages.common.edit')}}"><i class="material-icons">edit</i></a>

                                                            <a class="btn-floating btn-action waves-effect waves-light red tooltipped frmsubmit" href="{{route('blocked_number.destroy',[$blockedNumber->id])}}" data-position=top data-tooltip="{{__('messages.common.delete')}}" method="DELETE"><i class="material-icons">delete</i></a>

                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            <div class="input-field col s12">
                                                <button class="btn waves-effect waves-light red right submit" type="submit">{{__('messages.common.delete')}}<i class="mdi-content-send right"></i></button>
                                            </div>
                                        </form>
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
@endsection
@section('js')
<script src="{{asset('app-assets/vendors/data-tables/js/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('app-assets/vendors/data-tables/extensions/responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('app-assets/vendors/data-tables/js/dataTables.select.min.js')}}"></script>
<script>
    $('#page-length-option').DataTable({
        "responsive": true,
        "autoHeight": false,
        "scrollX": true,
        "lengthMenu": [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ]
    });
    $(document).ready(function() {
        $('body').addClass('loaded');

        $('#select_all').on('click', function() {
            if (this.checked) {
                $('.checkbox').each(function() {
                    this.checked = true;
                });
            } else {
                $('.checkbox').each(function() {
                    this.checked = false;
                });
            }
        });
        $('.checkbox').on('click', function() {
            if ($('.checkbox:checked').length == $('.checkbox').length) {
                $('#select_all').prop('checked', true);
            } else {
                $('#select_all').prop('checked', false);
            }
        });
        $('#block_numbers').validate({
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
    });
</script>
@endsection