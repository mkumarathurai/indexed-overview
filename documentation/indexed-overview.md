# Indexed Overview Documentation

## Project Hours Synchronization

### Overview
Systemet synkroniserer automatisk projekt timer fra Jira hver aften kl 23:00. Dette sikrer at vi har opdaterede data tilgængelige hver morgen, samtidig med at vi minimerer API kald til Jira.

### Komponenter

#### 1. Database Structure
- Tabel: `project_hours`
  - `project_key`: Jira projekt nøgle
  - `period`: År-måned (YYYY-MM format)
  - `monthly_hours`: Samlede timer for perioden
  - `invoice_ready_hours`: Timer klar til fakturering
  - `last_synced_at`: Tidspunkt for seneste sync

#### 2. Automated Sync Process
- Command: `projects:sync-hours`
- Kørsels tidspunkt: Dagligt kl 23:00
- Log fil: `storage/logs/projects-sync.log`

### Data Flow
1. **Daglig Synkronisering**
   - Henter alle aktive projekter
   - Opdaterer timer for den aktuelle måned
   - Opdaterer "Ready for Invoicing" timer

2. **Manuel Opdatering**
   - Via "Refresh Data" knappen i UI
   - Opdaterer kun den valgte måned
   - Gemmer resultatet i databasen

### Fejlhåndtering
- Alle sync fejl logges til `projects-sync.log`
- Email notifikationer ved fejl i sync
- Automatisk retry ved fejlet sync

### Cache Strategi
- Projekt liste: Cache i 60 minutter
- Timer per projekt: Cache i 60 minutter
- "Ready for Invoicing" status: Cache i 30 minutter

### Performance Optimering
- Batch processing af projekt data
- Intelligent caching af API responses
- Minimering af API kald via database mellemlagring

### Monitoring
For at overvåge sync processen:
```bash
# Check sync status
tail -f storage/logs/projects-sync.log

# Manuel kørsel af sync
php artisan projects:sync-hours
```

### Vedligeholdelse
Månedlig vedligeholdelse inkluderer:
1. Gennemgang af sync logs
2. Validering af time data
3. Oprydning i gamle cache entries
4. Backup af projekt timer

## PDF Generation

### Overview
Systemet kan generere PDF timesedler for projekter, der indeholder alle opgaver markeret som "Ready for invoicing".

### Komponenter

#### 1. PDF Controller & Service
- Controller: `ProjectPdfController`
- Service: `PdfService`
- Template: `timesheet.blade.php`

#### 2. PDF Features
- Projekt information
- Liste over fakturerbare opgaver
- Total timeforbrug
- Genererings tidspunkt
- Unik filnavngivning

#### 3. Brug
PDF'er kan genereres på to måder:
1. Via UI knappen "Download PDF" på projekt detalje siden
2. Via direkte URL: `/projects/{projectKey}/pdf`

#### 4. Teknisk Implementation
- Bruger barryvdh/laravel-dompdf package
- Cached rendering for bedre performance
- Responsivt layout der tilpasser sig sidebredden
- Proper håndtering af special karakterer

#### 5. PDF Layout
1. Header
   - Projekt navn og nøgle
   - Genererings dato og tid
2. Task Liste
   - Issue nøgle
   - Beskrivelse
   - Antal timer
3. Footer
   - Total antal timer
   - Noter om "Ready for invoicing" status

### Vedligeholdelse
- Template findes i `Resources/views/pdf/timesheet.blade.php`
- Styling er inline i templaten
- PDF generering logges i `storage/logs/laravel.log`

## PDF Timesheet Generation

### Overview
Systemet kan generere detaljerede timesedler i PDF format for hvert projekt. PDF'en indeholder kun opgaver med status "Ready for invoicing" og inkluderer relevante projekt- og periode-informationer.

### Komponenter

#### 1. PDF Controller & Service
- **Controller**: `ProjectPdfController`
  - Håndterer download requests
  - Henter projektdata og opgaver
  - Beregner faktureringsperiode

- **Service**: `PdfService`
  - Genererer PDF med proper formatering
  - Håndterer data aggregering
  - Styler output med custom CSS

#### 2. PDF Template
- Location: `Resources/views/pdf/timesheet.blade.php`
- Features:
  - Projekt header med key og navn
  - Opgaveliste med timer
  - Total sum af timer
  - Faktureringsperiode (månedsvis)
  - Genererings tidspunkt
  - Professionel styling

#### 3. Data Structure
PDF'en inkluderer:
- Projekt information
- Opgave detaljer:
  - Issue ID
  - Opgavebeskrivelse
  - Antal timer (2 decimaler)
- Metadata:
  - Faktureringsperiode (dd.mm.yyyy format)
  - Genererings tidspunkt
  - Status filter information

#### 4. Access Points
PDF'er kan genereres via:
- Projects index page (PDF ikon i actions kolonne)
- Direct URL: `/projects/{projectKey}/pdf`
- API endpoint for automatisk generering

### Usage
```php
// Generate PDF for specific project
$pdf = $pdfService->generateTimesheet($project, $invoiceReadyIssues, [
    'billingPeriod' => [
        'start' => '01.04.2025',
        'end' => '30.04.2025'
    ]
]);
```

### Styling
PDF'en bruger custom CSS for:
- Clean, professionel layout
- Proper table formatering
- Responsive tekststørrelser
- Konsistent spacing
- Læsbar typografi

### Maintenance
- Template findes i modulets Resources/views mappe
- Styling er inline i PDF templaten
- Periodeberegning håndteres i controlleren
- Timer formateres med 2 decimaler

## Udviklingsnoter

### Tilføjelse af Ny Funktionalitet
1. Opret migration hvis nødvendigt
2. Implementer ændringer i ProjectsApiService
3. Opdater tests
4. Dokumenter ændringer

### Test Miljø
```bash
# Setup test database
php artisan migrate --env=testing

# Kør tests
php artisan test --filter=ProjectsTest
```
