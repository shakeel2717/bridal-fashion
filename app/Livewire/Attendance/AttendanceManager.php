<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class AttendanceManager extends Component
{
    public int $month;

    public int $year;

    public string $filterRole = 'employee';

    public ?int $markUserId = null;

    public ?string $markDate = null;

    public string $markStatus = 'present';

    public string $markNote = '';

    public ?string $markUserName = null;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->subMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function nextMonth(): void
    {
        $nowMonth = (int) now()->format('m');
        $nowYear = (int) now()->format('Y');

        // Already on current month, can't go forward
        if ($this->year === $nowYear && $this->month === $nowMonth) {
            return;
        }

        if ($this->month === 12) {
            $this->month = 1;
            $this->year += 1;
        } else {
            $this->month += 1;
        }
    }

    public function openMarkModal(int $userId, string $date): void
    {
        if (Carbon::parse($date)->startOfDay()->gt(Carbon::now()->startOfDay())) {
            return;
        }

        $user = User::findOrFail($userId);
        $this->markUserId = $userId;
        $this->markDate = $date;
        $this->markUserName = $user->name;

        $existing = Attendance::where('user_id', $userId)
            ->where('date', $date)
            ->first();
        $this->markStatus = $existing?->status ?? 'present';
        $this->markNote = $existing?->note ?? '';

        $this->dispatch('open-mark-modal');
    }

    public function setMarkStatus(string $status): void
    {
        $this->markStatus = $status;
    }

    public function markAttendance(): void
    {
        $this->validate([
            'markStatus' => 'required|in:present,absent,half_day,leave',
            'markNote' => 'nullable|string|max:300',
        ]);

        $dateStr = Carbon::parse($this->markDate)->toDateString();

        Attendance::where('user_id', $this->markUserId)
            ->where('date', $dateStr)
            ->delete();

        Attendance::create([
            'user_id' => $this->markUserId,
            'date' => $dateStr,
            'status' => $this->markStatus,
            'note' => $this->markNote ?: null,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->closeMarkModal();
        $this->dispatch('close-mark-modal');
    }

    public function clearAttendance(): void
    {
        if (! $this->markUserId || ! $this->markDate) {
            return;
        }

        $dateStr = Carbon::parse($this->markDate)->toDateString();

        Attendance::where('user_id', $this->markUserId)
            ->where('date', $dateStr)
            ->delete();

        $this->closeMarkModal();
        $this->dispatch('close-mark-modal');
    }

    public function closeMarkModal(): void
    {
        $this->markUserId = null;
        $this->markDate = null;
        $this->markUserName = null;
        $this->markNote = '';
        $this->markStatus = 'present';
        $this->resetValidation();
    }

    public function markAllPresent(string $date): void
    {
        if (Carbon::parse($date)->startOfDay()->gt(Carbon::now()->startOfDay())) {
            return;
        }

        $dateStr = Carbon::parse($date)->toDateString();
        $employees = User::where('is_active', true)
            ->when($this->filterRole, fn ($q) => $q->where('role', $this->filterRole))
            ->get();

        foreach ($employees as $emp) {
            Attendance::where('user_id', $emp->id)
                ->where('date', $dateStr)
                ->delete();

            Attendance::create([
                'user_id' => $emp->id,
                'date' => $dateStr,
                'status' => 'present',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        }
    }

    public function render()
    {
        $employees = User::where('is_active', true)
            ->when($this->filterRole, fn ($q) => $q->where('role', $this->filterRole))
            ->orderBy('name')
            ->get();

        $daysInMonth = Carbon::createFromDate($this->year, $this->month, 1)->daysInMonth;
        $today = now()->toDateString();
        $currentMonth = Carbon::createFromDate($this->year, $this->month, 1);

        $attendances = Attendance::whereIn('user_id', $employees->pluck('id'))
            ->whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
            ->get()
            ->groupBy(fn ($a) => $a->user_id.'_'.Carbon::parse($a->date)->toDateString());

        $summary = [];
        foreach ($employees as $emp) {
            $summary[$emp->id] = [
                'present' => 0,
                'absent' => 0,
                'half_day' => 0,
                'leave' => 0,
            ];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr = Carbon::createFromDate($this->year, $this->month, $d)->toDateString();
                $key = $emp->id.'_'.$dateStr;
                if (isset($attendances[$key])) {
                    $status = $attendances[$key]->first()->status;
                    if (isset($summary[$emp->id][$status])) {
                        $summary[$emp->id][$status]++;
                    }
                }
            }
        }

        $nowMonth = (int) now()->format('m');
        $nowYear = (int) now()->format('Y');

        $canGoNext = ! ($this->year === $nowYear && $this->month === $nowMonth);

        return view('livewire.attendance.attendance-manager', compact(
            'employees', 'daysInMonth', 'today', 'attendances', 'summary', 'currentMonth', 'canGoNext'
        ));
    }
}
