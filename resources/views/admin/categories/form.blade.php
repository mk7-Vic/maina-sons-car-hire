<div class="booking-card">
    @if ($errors->any())
        <div class="form-errors">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $action }}">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div class="form-grid">
            <div class="form-group">
                <label for="name">Category Name</label>
                <input id="name" name="name" value="{{ old('name', $category->name) }}" required maxlength="255">
            </div>

            <div class="form-group full-width">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4">{{ old('description', $category->description) }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn">{{ $buttonText }}</button>
        <a href="{{ route('admin.categories.index') }}" class="btn">Cancel</a>
    </form>
</div>
