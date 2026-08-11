@if(session('success'))
    <div class="rounded-lg border border-success/20 bg-success/10 px-4 py-3 text-sm font-bold text-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="rounded-lg border border-danger/20 bg-danger/10 px-4 py-3 text-sm font-bold text-danger">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="rounded-lg border border-danger/20 bg-danger/10 px-4 py-3 text-sm font-bold text-danger">
        {{ $errors->first() }}
    </div>
@endif
