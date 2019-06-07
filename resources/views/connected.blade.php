@extends('layouts.master')

@section('content')
<div class="centerForm">
    <form class="details-form" method="POST" action="/disconnect">
        @csrf
        <label>
            To Disconnect please click the button
        </label>
        <br/>
        <button type="submit" class="btn btn-primary">Disconnect</button>
        {{--In a real app we would not be passing the storehash around like this--}}
        <input type="hidden" id="store_hash" name="store_hash" value="u8stgwcn9s">

    </form>
</div>
@stop