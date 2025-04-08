# Indexed Overview

Business intelligence dashboard der konsoliderer data fra forskellige kilder (Jira, Tempo, HubSpot) i et samlet overblik.

## Features

### Projects Module
- [x] Projekt oversigt med timer og fakturerbare timer
- [x] Månedlig timeregistrering (Juli 2024 - Juni 2025)
- [x] PDF generering af timesedler
- [x] Automatisk sync af projektdata (dagligt kl 23:00)
- [x] Dark/Light mode support

#### Seneste Tilføjelser
- PDF generation med faktureringsperiode (April 2024)
- Forbedret tabel layout med dansk tekst
- Automatisk timesync fra Jira
- Projekt detalje side med opgaveoversigt

#### Kommende Features
- [ ] Filtrering af projekter
- [ ] Export til Excel
- [ ] Email notifikationer ved timesync fejl
- [ ] Projekt statistik dashboard

### Database Struktur
```sql
project_hours
- project_key (string)
- period (YYYY-MM)
- monthly_hours (decimal)
- invoice_ready_hours (decimal)
- last_synced_at (timestamp)
```

### API Integration
- Jira API med token authentication
- Caching af API kald (60 min)
- Automatisk sync af timer
- Fejlhåndtering og logging

### Kommandoer
```bash
# Sync projekt timer
php artisan projects:sync-hours

# Kør scheduler lokalt
php artisan schedule:work
```

### Environment Variabler
```env
JIRA_BASE_URL=https://your-domain.atlassian.net
JIRA_EMAIL=your-email@domain.com
JIRA_API_TOKEN=your-api-token
```

## Documentation
Se den komplette dokumentation i:
- `/documentation/indexed-overview.md`
- `/documentation/projects-module.md`
