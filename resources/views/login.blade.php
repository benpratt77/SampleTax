@extends('layouts.master')

@section('content')
<div class="centerForm" >
    <form class="details-form" method="POST" action="/update-credentials">
        @csrf
        <div class="form-group">
            <label for="userName">Email address</label>
            <input type="email"  name="username" class="form-control" id="userName" aria-describedby="emailHelp" placeholder="Enter email">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" class="form-control" id="password" placeholder="Password">
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
        {{--In a real app we would not be passing the storehash around like this. this is just for a quick demo of how this works--}}
        <input type="hidden" id="store_hash" name="store_hash" value="u8stgwcn9s">
    </form>
</div>
@stop