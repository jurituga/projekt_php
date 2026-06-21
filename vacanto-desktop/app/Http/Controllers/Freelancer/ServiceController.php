<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceAvailability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = auth()->user()->services()->latest()->get();

        return view('freelancer.services.index', compact('services'));
    }

    public function create(): View
    {
        $profile = auth()->user()->freelancerProfile;
        $isScheduled = $profile?->isScheduled() ?? false;

        return view('freelancer.services.form', [
            'service' => null,
            'isScheduled' => $isScheduled,
            'freelancerType' => $profile?->freelancer_type ?? 'general',
            'slotsByDate' => [],
            'timeOptions' => $this->timeOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedServiceData($request, false);

        $service = auth()->user()->services()->create($validated);

        return redirect()->route('freelancer.services.edit', $service)
            ->with('success', 'Service created. Add your availability dates and times below.');
    }

    public function edit(Service $service): View
    {
        abort_unless($service->freelancer_id === auth()->id(), 404);

        $profile = auth()->user()->freelancerProfile;
        $isScheduled = $profile?->isScheduled() ?? false;

        $slotsByDate = [];
        $slots = $service->availability()
            ->where('available_date', '>=', now()->toDateString())
            ->orderBy('available_date')
            ->orderBy('slot_time')
            ->get();

        foreach ($slots as $slot) {
            $slotsByDate[$slot->available_date->format('Y-m-d')][] = $slot;
        }

        return view('freelancer.services.form', [
            'service' => $service,
            'isScheduled' => $isScheduled,
            'freelancerType' => $profile?->freelancer_type ?? 'general',
            'slotsByDate' => $slotsByDate,
            'timeOptions' => $this->timeOptions(),
        ]);
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        abort_unless($service->freelancer_id === auth()->id(), 404);

        $service->update($this->validatedServiceData($request, true));

        return redirect()->route('freelancer.services.edit', $service)
            ->with('success', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        abort_unless($service->freelancer_id === auth()->id(), 404);

        $service->delete();

        return redirect()->route('freelancer.services.index')->with('success', 'Service deleted.');
    }

    public function addSlots(Request $request, Service $service): RedirectResponse
    {
        abort_unless($service->freelancer_id === auth()->id(), 404);

        $validated = $request->validate([
            'slot_date' => ['required', 'date', 'after_or_equal:today'],
            'slot_times' => ['required', 'array', 'min:1'],
            'slot_times.*' => ['regex:/^\d{2}:\d{2}$/'],
        ]);

        $added = 0;
        foreach ($validated['slot_times'] as $time) {
            $slot = ServiceAvailability::firstOrCreate([
                'service_id' => $service->id,
                'available_date' => $validated['slot_date'],
                'slot_time' => $time.':00',
            ], [
                'is_booked' => false,
            ]);

            if ($slot->wasRecentlyCreated) {
                $added++;
            }
        }

        $message = $added > 0 ? "{$added} slot(s) added." : 'No new slots added (duplicates skipped).';

        return redirect()->route('freelancer.services.edit', $service)->with('success', $message);
    }

    public function removeSlot(Service $service, ServiceAvailability $slot): RedirectResponse
    {
        abort_unless($service->freelancer_id === auth()->id(), 404);
        abort_unless($slot->service_id === $service->id, 404);
        abort_if($slot->is_booked, 422, 'Cannot remove a booked slot.');

        $slot->delete();

        return redirect()->route('freelancer.services.edit', $service)->with('success', 'Slot removed.');
    }

    private function validatedServiceData(Request $request, bool $isEdit): array
    {
        $rules = [
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_type' => ['required', 'in:fixed,hourly'],
        ];

        if ($isEdit) {
            $rules['status'] = ['required', 'in:active,inactive'];
        }

        $validated = $request->validate($rules);

        if (! $isEdit) {
            $validated['status'] = 'active';
        }

        return $validated;
    }

    private function timeOptions(): array
    {
        $options = [];
        for ($h = 6; $h <= 21; $h++) {
            $options[] = sprintf('%02d:00', $h);
            if ($h < 21) {
                $options[] = sprintf('%02d:30', $h);
            }
        }

        return $options;
    }
}
