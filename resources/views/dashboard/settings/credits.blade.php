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
                <h4>Cachet</h4>

                <p>{!! trans('dashboard.settings.credits.license') !!} This version you are currently viewing has been severely edited.</p>

                <hr>
            </div>
        </div>
    </div>
</div>
@stop
