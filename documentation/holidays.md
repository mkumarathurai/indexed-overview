# Holidays Module

## 📦 Overview
The **Holidays Module** is part of an internal Laravel application using a modular architecture. This module is responsible for:

- Retrieving holiday data for employees from Jira (Epic `internal-9` and its subtasks).
- Calculating holiday hours per employee.
- Displaying a table of holiday dates per employee.
- Showing a total number of holidays taken (assuming 7.5 hours per day).

---

## 🧱 Modular Laravel Approach
This project follows a **modular structure** for better maintainability, reusability, and separation of concerns. Each module is self-contained and can be plugged into the core Laravel app.

Typical structure of a module (like `Holidays`):

```
modules/
├── Holidays/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── HolidayController.php
│   │   └── Requests/
│   ├── Models/
│   │   └── Holiday.php
│   ├── Services/
│   │   └── JiraHolidayService.php
│   ├── Views/
│   │   └── index.blade.php
│   ├── routes/
│   │   └── web.php
│   └── Providers/
│       └── HolidaysServiceProvider.php
```

Each module is registered in the app via its `ServiceProvider`.

---

## 🔌 Jira & Tempo Integration
The module connects to Jira to fetch subtasks under the Epic `internal-9`. However, Jira does **not** provide full user information directly within the task data.

### Subtask Fields Expected from Jira:
- **Issue ID**
- **Summary / Description**
- **Date**

### 🕑 Hours & User Details via Tempo Timesheets
Holiday hours are logged using the **Tempo Timesheets** add-on in Jira. To get actual hours and user identification, the module uses the **Tempo REST API**:

- Endpoint: `https://api.eu.tempo.io`
- Required headers: `Authorization: Bearer <API_TOKEN>`
- Endpoint used: `/4/worklogs` and `/4/users` to match user ID to email/name

### Example Flow:
1. Fetch all worklogs associated with `internal-9` Epic using Tempo API.
2. Extract `userId`, `date`, and `timeSpentSeconds`.
3. Resolve `userId` to email/name using `/4/users`.
4. Normalize and store in Laravel models.

---

## 📊 Holiday Table Output
The frontend view (Blade) will generate a dynamic table like:

| Employee       | Date       | Hours | Day Count |
|----------------|------------|--------|-----------|
| John Doe       | 2025-04-03 | 7.5    | 1         |
| John Doe       | 2025-04-04 | 4.5    | 0.6       |
| **Total Days** |            |        | **1.6**   |

### Business Rule:
- **1 full day = 7.5 hours**
- Partial days calculated as `(hours / 7.5)`

---

## 🧮 Logic Summary
The main logic resides in `JiraHolidayService`:

```php
public function getEmployeeHolidaySummary(): Collection
{
    $worklogs = $this->fetchWorklogsFromTempo();
    $users = $this->resolveUserIdsToEmails();

    return $worklogs->groupBy('employee')->map(function ($entries) {
        return [
            'entries' => $entries,
            'total_days' => round($entries->sum('hours') / 7.5, 1)
        ];
    });
}
```

---

## 📍 Routes
Defined in `modules/Holidays/routes/web.php`:

```php
Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
```

---

## 📅 UI View (Blade)
The UI lists each employee’s holiday records with their respective total. Sorting and filtering can be added later.

---

## 🧪 Future Enhancements
- Add CSV export for reporting.
- Filter by date range.
- Email notification summary.
- Frontend filters and live search.

---

## ✅ Example Output
```
Employee: Jane Smith
---------------------------------
2025-04-01 - 7.5h
2025-04-02 - 3.0h
Total: 1.4 days
```

---

## 👥 Maintainers
- This module is managed by the internal dev team.
- Epic reference: `internal-9`

---

> Built with ❤️ using Laravel and a clean modular approach.
