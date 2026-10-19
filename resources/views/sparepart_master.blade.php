@extends('layouts.app')

@section('content')

@include('sparepart_master.styles.page')

@include('sparepart_master.sections.header')

@include('sparepart_master.sections.upload')

@include('sparepart_master.sections.saved_list')

@include('sparepart_master.modals.preview')

<script>
    @include('sparepart_master.scripts.01_helpers_upload_preview')
        @include('sparepart_master.scripts.02_change_confirm_inline_edit')
</script>

@endsection