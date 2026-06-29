<x-filament-panels::page>

<style>
/* ═══════════════════════════════════════════
   Student Progress Report – Embedded Styles
   ═══════════════════════════════════════════ */
.spr-wrap { display: flex; flex-direction: column; gap: 20px; }

/* ── Card base ── */
.spr-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
}
.dark .spr-card {
    background: #111827;
    border-color: #374151;
}

/* ── Gradient top bars ── */
.spr-bar-violet { height: 5px; background: linear-gradient(90deg, #7c3aed, #3b82f6, #06b6d4); border-radius: 16px 16px 0 0; }
.spr-bar-green  { height: 4px; background: linear-gradient(90deg, #10b981, #06b6d4); border-radius: 16px 16px 0 0; }

/* ── Card body ── */
.spr-card-body { padding: 24px; }

/* ── Header row ── */
.spr-header-row { display: flex; align-items: flex-start; gap: 16px; }
.spr-icon-wrap {
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    width: 44px; height: 44px; border-radius: 12px;
    background: linear-gradient(135deg, #7c3aed, #3b82f6);
    box-shadow: 0 4px 12px rgba(59,130,246,.3);
}
.spr-icon-wrap svg { display: block; width: 20px; height: 20px; }
.spr-header-meta { flex: 1; min-width: 0; }
.spr-eyebrow {
    font-size: 11px; font-weight: 700; letter-spacing: .08em;
    text-transform: uppercase; color: #7c3aed; margin: 0;
}
.dark .spr-eyebrow { color: #a78bfa; }
.spr-h2 { margin: 4px 0 0; font-size: 15px; font-weight: 700; color: #111827; }
.dark .spr-h2 { color: #f9fafb; }
.spr-sub { margin: 4px 0 0; font-size: 13px; color: #6b7280; line-height: 1.5; }
.dark .spr-sub { color: #9ca3af; }

/* ── Divider ── */
.spr-divider { border: none; border-top: 1px solid #f3f4f6; margin: 20px 0; }
.dark .spr-divider { border-top-color: #1f2937; }

/* ── Student info card ── */
.spr-student-row { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.spr-avatar {
    flex-shrink: 0;
    width: 48px; height: 48px; border-radius: 50%;
    background: linear-gradient(135deg, #60a5fa, #7c3aed);
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; font-weight: 700; color: #fff;
    box-shadow: 0 2px 8px rgba(99,102,241,.35);
}
.spr-student-meta { flex: 1; min-width: 0; }
.spr-student-name { font-size: 15px; font-weight: 700; color: #111827; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.dark .spr-student-name { color: #f9fafb; }
.spr-teacher { font-size: 13px; color: #6b7280; margin: 2px 0 0; }
.dark .spr-teacher { color: #9ca3af; }
.spr-teacher strong { color: #7c3aed; font-weight: 600; }
.dark .spr-teacher strong { color: #a78bfa; }
.spr-teacher em { font-style: italic; color: #9ca3af; }

/* ── Badges ── */
.spr-badge {
    display: inline-flex; align-items: center; gap: 5px;
    border-radius: 999px; padding: 4px 12px;
    font-size: 12px; font-weight: 600; white-space: nowrap;
}
.spr-badge svg { display: block; width: 12px; height: 12px; flex-shrink: 0; }
.spr-badge-green {
    background: #ecfdf5; color: #065f46;
    box-shadow: inset 0 0 0 1px rgba(16,185,129,.25);
}
.dark .spr-badge-green { background: #022c22; color: #6ee7b7; }
.spr-badge-amber {
    background: #fffbeb; color: #92400e;
    box-shadow: inset 0 0 0 1px rgba(245,158,11,.25);
}
.dark .spr-badge-amber { background: #1c1405; color: #fcd34d; }

/* ── Empty state ── */
.spr-empty {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; text-align: center; gap: 16px;
    padding: 48px 24px;
    background: #f9fafb;
    border: 2px dashed #e5e7eb;
    border-radius: 16px;
}
.dark .spr-empty {
    background: #0d1117;
    border-color: #374151;
}
.spr-empty-icon {
    width: 64px; height: 64px; border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.spr-empty-icon svg { display: block; width: 32px; height: 32px; }
.spr-empty-icon-amber { background: #fef3c7; }
.dark .spr-empty-icon-amber { background: #1c1405; }
.spr-empty-icon-gray  { background: #f3f4f6; }
.dark .spr-empty-icon-gray  { background: #1f2937; }
.spr-empty-title { font-size: 15px; font-weight: 700; color: #1f2937; margin: 0; }
.dark .spr-empty-title { color: #f9fafb; }
.spr-empty-text { font-size: 13px; color: #6b7280; max-width: 360px; margin: 6px 0 0; line-height: 1.6; }
.dark .spr-empty-text { color: #6b7280; }

/* ── CTA Button ── */
.spr-btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 22px; border-radius: 12px;
    background: linear-gradient(135deg, #7c3aed, #3b82f6);
    color: #fff; font-size: 13px; font-weight: 600;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(99,102,241,.35);
    transition: opacity .2s, box-shadow .2s;
}
.spr-btn:hover { opacity: .88; box-shadow: 0 6px 20px rgba(99,102,241,.45); }
.spr-btn svg { display: block; width: 15px; height: 15px; flex-shrink: 0; }

/* ── Chart label ── */
.spr-chart-label {
    font-size: 11px; font-weight: 700; letter-spacing: .08em;
    text-transform: uppercase; color: #6b7280; margin: 0 0 16px;
}
.dark .spr-chart-label { color: #9ca3af; }
</style>

<div class="spr-wrap">

    {{-- ── Header card ── --}}
    <div class="spr-card">
        <div class="spr-bar-violet"></div>
        <div class="spr-card-body">

            <div class="spr-header-row">
                <div class="spr-icon-wrap">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                </div>
                <div class="spr-header-meta">
                    <p class="spr-eyebrow">Phân tích &amp; Theo dõi</p>
                    <h2 class="spr-h2">{{ trans('packages.planning_evaluation::planning.progress.title') }}</h2>
                    <p class="spr-sub">Chọn học sinh để xem biểu đồ tiến độ đánh giá theo từng buổi học.</p>
                </div>
            </div>

            <hr class="spr-divider">

            <form>{{ $this->form }}</form>
        </div>
    </div>

    {{-- ── Nội dung chính ── --}}
    @if ($this->studentId)
        @php
            $student   = \Quochao56\Student\Models\Student::find($this->studentId);
            $teacher   = $student?->currentTeacher;

            $evalQuery = \Quochao56\PlanningEvaluation\Models\Evaluation::query()
                ->whereHas('planning', fn ($q) => $q->where('student_id', $this->studentId));
            if ($this->dateFrom) {
                $evalQuery->whereDate('created_at', '>=', $this->dateFrom);
            }
            if ($this->dateTo) {
                $evalQuery->whereDate('created_at', '<=', $this->dateTo);
            }
            $hasEvaluations = (clone $evalQuery)->exists();
            $evalCount      = (clone $evalQuery)->count();
        @endphp

        {{-- Thông tin học sinh --}}
        <div class="spr-card">
            <div class="spr-card-body">
                <div class="spr-student-row">
                    <div class="spr-avatar">{{ mb_substr($student?->name ?? '?', 0, 1) }}</div>
                    <div class="spr-student-meta">
                        <p class="spr-student-name">{{ $student?->name }}</p>
                        <p class="spr-teacher">
                            Giáo viên phụ trách:
                            @if ($teacher)
                                <strong>{{ $teacher->name }}</strong>
                            @else
                                <em>Chưa phân công</em>
                            @endif
                        </p>
                    </div>
                    @if ($hasEvaluations)
                        <span class="spr-badge spr-badge-green">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                            </svg>
                            {{ $evalCount }} đánh giá
                        </span>
                    @else
                        <span class="spr-badge spr-badge-amber">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                            </svg>
                            Chưa có đánh giá
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Biểu đồ hoặc Empty state --}}
        @if ($hasEvaluations)
            <div class="spr-card">
                <div class="spr-bar-green"></div>
                <div class="spr-card-body">
                    <p class="spr-chart-label">
                        {{ trans('packages.planning_evaluation::planning.progress.chart_heading', ['name' => $student?->name]) }}
                    </p>
                    @livewire(\Quochao56\PlanningEvaluation\Filament\Widgets\StudentProgressChart::class,
                        ['studentId' => $this->studentId, 'dateFrom' => $this->dateFrom, 'dateTo' => $this->dateTo],
                        key('progress-chart-' . $this->studentId . '-' . $this->dateFrom . '-' . $this->dateTo))
                </div>
            </div>
        @else
            @php
                $createRoute = \Quochao56\PlanningEvaluation\Filament\Resources\Plannings\PlanningResource::getUrl('create', [
                    'student_id' => $this->studentId,
                ]);
            @endphp
            <div class="spr-empty">
                <div class="spr-empty-icon spr-empty-icon-amber">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#f59e0b">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <div>
                    <p class="spr-empty-title">Chưa có dữ liệu tiến độ</p>
                    <p class="spr-empty-text">{{ trans('packages.planning_evaluation::planning.progress.no_evaluations') }}</p>
                </div>
                <a href="{{ $createRoute }}" class="spr-btn">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tạo kế hoạch &amp; đánh giá ngay
                </a>
            </div>
        @endif

    @else
        {{-- Chưa chọn học sinh --}}
        <div class="spr-empty">
            <div class="spr-empty-icon spr-empty-icon-gray">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#9ca3af">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div>
                <p class="spr-empty-title">Chọn học sinh để bắt đầu</p>
                <p class="spr-empty-text">{{ trans('packages.planning_evaluation::planning.progress.empty_state') }}</p>
            </div>
        </div>
    @endif

</div>

</x-filament-panels::page>
