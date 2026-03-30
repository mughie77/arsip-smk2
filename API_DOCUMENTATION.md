# API Documentation for Sistem Informasi Arsip Digital

Sistem Informasi Arsip Digital menyediakan API sederhana untuk membaca dan menulis data dari aplikasi lain.

## Autentikasi

API menggunakan **API Key** sederhana untuk keamanan. Anda harus menyertakan kunci ini di setiap permintaan.

- **API Key**: Ganti dengan kunci yang Anda atur di `api/config.php`.
- **Metode**:
  - Melalui Header: `X-API-KEY: YOUR-API-KEY`
  - Melalui Parameter GET: `?api_key=YOUR-API-KEY`

## Base URL

`http://[domain-anda]/api/v1.php`

---

## Endpoints

Gunakan parameter `resource` untuk menentukan data yang ingin diakses.

### Resource yang Tersedia:
- `surat_masuk`
- `surat_keluar`
- `notulen`
- `klasifikasi`
- `arsip_berkas`

---

### 1. List Data (GET)

Mengambil semua data dari sebuah resource.

**Contoh Permintaan:**
`GET /api/v1.php?resource=surat_masuk&api_key=YOUR-API-KEY`

**Respons Sukses (200 OK):**
```json
[
  {
    "id": 1,
    "nomor_arsip": "SM-20241024-ABC123",
    "nomor_surat": "001/SM/X/2024",
    "perihal": "Undangan Rapat",
    ...
  }
]
```

---

### 2. Detail Data (GET)

Mengambil satu data berdasarkan ID.

**Contoh Permintaan:**
`GET /api/v1.php?resource=surat_masuk&id=1&api_key=YOUR-API-KEY`

**Respons Sukses (200 OK):**
```json
{
  "id": 1,
  "nomor_arsip": "SM-20241024-ABC123",
  "nomor_surat": "001/SM/X/2024",
  "perihal": "Undangan Rapat",
  ...
}
```

---

### 3. Tambah Data (POST)

Menambahkan data baru ke dalam database. Kirimkan data dalam format JSON di body permintaan.

**Contoh Permintaan:**
`POST /api/v1.php?resource=surat_masuk&api_key=YOUR-API-KEY`

**Body JSON:**
```json
{
  "nomor_arsip": "SM-20241024-NEW001",
  "nomor_surat": "002/SM/X/2024",
  "perihal": "Pemberitahuan",
  "asal_surat": "Dinas Pendidikan",
  "tanggal_diterima": "2024-10-24",
  "nama_file_pdf": "surat_002.pdf"
}
```

**Respons Sukses (201 Created):**
```json
{
  "message": "Record created successfully.",
  "id": 2
}
```

---

## Kode Status HTTP

- `200 OK`: Permintaan berhasil.
- `201 Created`: Data berhasil ditambahkan.
- `400 Bad Request`: Parameter kurang atau JSON tidak valid.
- `401 Unauthorized`: API Key salah atau tidak ada.
- `404 Not Found`: Resource atau ID tidak ditemukan.
- `405 Method Not Allowed`: Metode HTTP selain GET/POST digunakan.
- `500 Internal Server Error`: Terjadi kesalahan pada server/database.
