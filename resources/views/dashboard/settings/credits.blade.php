@extends('layout.dashboard')

@section('content')
<div class="content-panel">
    @includeWhen(isset($subMenu), 'dashboard.partials.sub-sidebar')
    <div class="content-wrapper">
        <div class="header sub-header" id="application-setup">
            <span class="uppercase">
                {{ trans('dashboard.settings.credits.credits') }}
            </span>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <h4>Enreach Status</h4>

                <p>Enreach Status Page. Much nice! Very wow!</p>

                <hr>
            </div>
        </div>
    </div>
</div>
@stop
