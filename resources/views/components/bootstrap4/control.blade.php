@include('genealabs-laravel-casts::components.bootstrap4.form-group-open')

@if($type !== 'checkbox' && $type !== 'submit')
    @include('genealabs-laravel-casts::components.bootstrap4.label')
@endif

@include("genealabs-laravel-casts::components.bootstrap4.{$type}")
@include('genealabs-laravel-casts::components.bootstrap4.form-group-close')
