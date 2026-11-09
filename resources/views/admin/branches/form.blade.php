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
                <label for="name">Branch Name</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $branch?->name) }}"
                    maxlength="255"
                    required
                >
            </div>

            <div class="form-group">
                <label for="location">Location</label>
                <input
                    id="location"
                    name="location"
                    type="text"
                    value="{{ old('location', $branch?->location) }}"
                    maxlength="255"
                    required
                >
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input
                    id="phone"
                    name="phone"
                    type="tel"
                    value="{{ old('phone', $branch?->phone) }}"
                    maxlength="30"
                >
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $branch?->email) }}"
                    maxlength="255"
                >
            </div>

            <div class="form-group">
                <label for="is_active">Branch Status</label>
                <select id="is_active" name="is_active" required>
                    <option value="1" @selected((string) old('is_active', $branch?->is_active ?? 1) === '1')>
                        Active
                    </option>
                    <option value="0" @selected((string) old('is_active', $branch?->is_active ?? 1) === '0')>
                        Inactive
                    </option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn">{{ $buttonText }}</button>

        <a href="{{ route('admin.branches.index') }}" class="btn">
            Cancel
        </a>
    </form>
</div>