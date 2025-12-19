# Maps Link Feature Documentation

## Overview
This feature allows users to automatically extract latitude and longitude coordinates from Google Maps links when adding or editing UMKM data.

## How it Works

### 1. **Google Maps Link Field**
- When creating or editing UMKM data, there's now a "Link Google Maps" field
- Users can paste any Google Maps link into this field
- The system will automatically extract latitude and longitude coordinates

### 2. **Supported Link Formats**
The system can extract coordinates from various Google Maps URL formats:

- Standard share links: `https://maps.app.goo.gl/...`
- Direct coordinate links: `https://www.google.com/maps/@-8.6500000,115.2167000,17z`
- Place links: `https://www.google.com/maps/place/Location/@-8.6500000,115.2167000`
- Search results: `https://www.google.com/maps/search/-8.6500000,115.2167000`

### 3. **Example Usage**

#### Getting a Google Maps Link:
1. Open Google Maps
2. Navigate to the desired location
3. Click "Share" button
4. Copy the generated link
5. Paste it in the "Link Google Maps" field

#### Common Google Maps Link Examples:
```
https://maps.app.goo.gl/abcdefghijk123
https://www.google.com/maps/@-8.6500000,115.2167000,17z
https://www.google.com/maps/place/Denpasar/@-8.6500000,115.2167000,12z
```

### 4. **Features**

#### Automatic Extraction:
- Paste link → coordinates filled automatically
- Real-time extraction as you type/paste
- Success notification when coordinates are extracted
- Warning if link format is not recognized

#### Manual Edit Option:
- "Edit Manual" button to unlock coordinate fields
- "Kunci" button to lock coordinates again
- Prevents accidental coordinate changes

#### Validation:
- URL validation for Maps link field
- Coordinate range validation (latitude: -90 to 90, longitude: -180 to 180)
- Error messages for invalid inputs

### 5. **User Interface**

#### Create Form:
- Link Google Maps field with URL input
- Locked coordinate fields (auto-filled from Maps link)
- Toggle button for manual coordinate editing

#### Edit Form:
- Same functionality as create form
- Maps link field is reset when opening edit dialog
- Existing coordinates are preserved if no new Maps link is provided

### 6. **Error Handling**
- Invalid URLs show validation error
- Unrecognizable Maps links show warning toast
- Network errors are caught and displayed
- Coordinates are cleared if Maps link is removed

## Technical Implementation

### Frontend (Vue.js):
- `extractCoordinatesFromLink()` function handles coordinate extraction
- Multiple regex patterns for different Maps URL formats
- SweetAlert2 notifications for user feedback
- Reactive form handling with validation

### Backend (Laravel):
- `maps_link` validation as nullable URL
- Coordinates stored as decimal fields in database
- No server-side processing of Maps links (client-side only)

## Benefits
1. **User-Friendly**: No need to manually find coordinates
2. **Accurate**: Directly from Google Maps source
3. **Fast**: Instant extraction and filling
4. **Flexible**: Supports various Maps link formats
5. **Safe**: Manual override available if needed
