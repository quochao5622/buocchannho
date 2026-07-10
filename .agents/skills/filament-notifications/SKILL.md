---
name: filament-notifications
description: >
  Hướng dẫn cách gửi Filament toast notifications đúng cách trong dự án này.
  Dùng khi tạo mới hoặc chỉnh sửa bất kỳ Livewire component, Filament Page,
  Widget, Table action, hoặc Filament Action class nào cần gửi notification.
  Tránh bug notification không hiển thị ngay mà phải reload trang mới thấy.
---

# Filament Notifications — Cách Gửi Đúng Cách

## Vấn đề (Root Cause)

Filament `Notification::make()->send()` ghi notification vào **session**.
Filament's `Notifications` Livewire component chỉ đọc session khi nhận được
event `notificationsSent`. Event này được dispatch qua Livewire's `dehydrate`
hook trong `NotificationsServiceProvider`, nhưng **không đáng tin cậy** trong
mọi context — dẫn đến notifications tích lũy trong session và chỉ hiển thị
khi reload trang (full page load gọi `mount()` đọc toàn bộ session).

**Kết quả**: User thấy nhiều toast cùng lúc khi reload thay vì từng toast
hiện ngay sau mỗi action.

## Quy tắc bắt buộc

> Sau MỌI lần gọi `Notification::make()->...->send()`, PHẢI dispatch thêm
> event `notificationsSent` để đảm bảo notification hiển thị ngay lập tức.

Có 3 cách tùy theo loại class:

---

## Cách 1: Livewire Component / Filament Page / Widget

Dùng cho: `ListRecords`, `EditRecord`, `CreateRecord`, `Widget`, hoặc bất kỳ
class nào extends Livewire component.

**Bước 1**: Import và add trait `HasNotifications`:

```php
use Quochao56\Core\Traits\HasNotifications;

class MyPage extends ListRecords
{
    use HasNotifications;
    // ...
}
```

**Bước 2**: Thay `->send()` bằng `$this->notify()`:

```php
// ❌ Sai — notification có thể không hiển thị ngay
Notification::make()
    ->title('Thành công!')
    ->success()
    ->send();

// ✅ Đúng — notification hiển thị ngay lập tức
$this->notify(
    Notification::make()
        ->title('Thành công!')
        ->success()
);
```

Nếu muốn dispatch thủ công (không dùng trait):

```php
Notification::make()->title('...')->success()->send();
$this->dispatch('notificationsSent');
```

---

## Cách 2: Table Action Closures (trong Table class)

Dùng cho: `Tables/EntityTable.php` — bên trong `->action(function(...) {})`.

Thêm `$livewire` vào signature của closure và dispatch sau `send()`:

```php
// ❌ Sai
->action(function (MyModel $record) {
    // ...
    Notification::make()->title('Duyệt thành công!')->success()->send();
}),

// ✅ Đúng
->action(function (MyModel $record, $livewire) {
    // ...
    Notification::make()->title('Duyệt thành công!')->success()->send();
    $livewire->dispatch('notificationsSent');
}),
```

Nếu closure đã có `array $data`:

```php
->action(function (MyModel $record, array $data, $livewire) {
    // ...
    Notification::make()->title('...')->send();
    $livewire->dispatch('notificationsSent');
}),
```

---

## Cách 3: Filament Action Class (extends `Filament\Actions\Action`)

Dùng cho: standalone Action classes như `ApproveAction`, `ChangePassword`, v.v.

Dùng `$this->getLivewire()->dispatch()`:

```php
class ApproveAction extends Action
{
    public function handle(Model $record): void
    {
        try {
            $record->update(['status' => 'approved']);

            Notification::make()->title('Duyệt thành công!')->success()->send();
            $this->getLivewire()->dispatch('notificationsSent'); // ✅

        } catch (\Throwable $th) {
            Notification::make()->title('Có lỗi xảy ra.')->danger()->send();
            $this->getLivewire()->dispatch('notificationsSent'); // ✅
            Log::error($th);
        }
    }
}
```

---

## HasNotifications Trait

File: `packages/core/src/Traits/HasNotifications.php`

```php
<?php

namespace Quochao56\Core\Traits;

use Filament\Notifications\Notification;

trait HasNotifications
{
    protected function notify(Notification $notification): void
    {
        $notification->send();
        $this->dispatch('notificationsSent');
    }
}
```

Trait này chỉ dùng được trong **Livewire components** (có `$this->dispatch()`).
Không dùng được trong Table classes hay standalone Action classes.

---

## Trường hợp ngoại lệ: Redirect sau notification

Nếu sau `->send()` có `->redirect()` hoặc `$this->redirect()`, notification sẽ
tự hiển thị sau redirect do `mount()` đọc session. **KHÔNG cần** dispatch thêm:

```php
// Trường hợp này OK, không cần dispatch
Notification::make()->title('Đã lưu!')->success()->send();
$this->redirect(MyResource::getUrl('index')); // redirect tự xử lý
```

---

## Checklist khi viết code mới

- [ ] Class là Livewire component? → Dùng `HasNotifications` trait + `$this->notify()`
- [ ] Closure trong Table/Form class? → Thêm `$livewire` vào signature + `$livewire->dispatch('notificationsSent')`
- [ ] Standalone Action class (extends `Action`)? → Dùng `$this->getLivewire()->dispatch('notificationsSent')`
- [ ] Sau notification có redirect? → Không cần dispatch thêm
