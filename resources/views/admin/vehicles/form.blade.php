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
                <label for="vehicle_category_id">Category</label>
                <select id="vehicle_category_id" name="vehicle_category_id" required>
                    <option value="">Choose category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('vehicle_category_id', $vehicle?->vehicle_category_id) == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @foreach ([
                'registration_number' => 'Registration Number',
                'make' => 'Make',
                'model' => 'Model',
                'year' => 'Year',
                'color' => 'Color',
                'transmission' => 'Transmission',
                'fuel_type' => 'Fuel Type',
                'seats' => 'Seats',
                'daily_rate' => 'Daily Rate (KSh)',
            ] as $field => $label)
                <div class="form-group">
                    <label for="{{ $field }}">{{ $label }}</label>
                    <input
                        id="{{ $field }}"
                        name="{{ $field }}"
                        value="{{ old($field, $vehicle?->$field) }}"
                        @if (in_array($field, ['registration_number', 'make', 'model', 'seats', 'daily_rate'])) required @endif
                        @if (in_array($field, ['year', 'seats', 'daily_rate'])) type="number" @else type="text" @endif
                        @if ($field === 'year') min="1900" max="{{ now()->year + 1 }}" @endif
                        @if ($field === 'seats') min="1" max="255" @endif
                        @if ($field === 'daily_rate') min="0.01" step="0.01" @endif
                    >
                </div>
            @endforeach

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach (['available', 'maintenance', 'unavailable'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $vehicle?->status ?? 'available') === $status)>
                            {{ ucfirst($status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group full-width">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4">{{ old('description', $vehicle?->description) }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn">{{ $buttonText }}</button>
        <a href="{{ route('admin.vehicles.index') }}" class="btn">Cancel</a>
    </form>
</div>
