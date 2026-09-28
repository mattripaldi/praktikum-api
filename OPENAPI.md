# Panduan OpenAPI — API Blog (Praktikum)

Dokumen ini menjelaskan cara membuat dokumentasi API (OpenAPI) untuk endpoint **Blog** di project ini, langkah demi langkah. Semua contoh diambil langsung dari `routes/api.php` dan ditulis dalam format **JSON** (`openapi.json`).

---

## Quick Start — Coba API dengan Mock Server

Sebelum menulis dokumentasi, kita bisa langsung mencoba API memakai **mock server** dari Prism. Prism membaca file `openapi.json` (isi lengkapnya ada di bagian 5) lalu membalas setiap request dengan contoh data (`example`) yang ada di dalamnya, jadi backend tidak perlu jalan. Syaratnya: **Node.js/npm** sudah terpasang.

Jalankan di root project:

```bash
npx @stoplight/prism-cli mock openapi.json -p 4040
```

Penjelasan tiap bagian perintah:

| Bagian | Artinya |
| --- | --- |
| `npx` | Menjalankan tool Node.js tanpa perlu install permanen. |
| `@stoplight/prism-cli` | Nama package mock server-nya. |
| `mock` | Mode Prism untuk membalas request dengan data contoh (bukan proxy ke server asli). |
| `openapi.json` | File spesifikasi yang dibaca Prism. |
| `-p 4040` | Port server mock. Kalau tidak ditulis, Prism memakai default `4010`. |

Setelah server jalan, endpoint bisa dicoba langsung (sesuai `paths` yang ada di file, jadi tanpa prefix `/api`):

```bash
curl http://localhost:4040/blogs
curl http://localhost:4040/blog/1
```

Contoh respons yang keluar adalah nilai dari field `example`, misalnya:

```json
{
  "data": [
    { "id": 1, "title": "Belajar Laravel", "content": "Membuat REST API sederhana", "created_at": "2026-08-31T07:40:00.000000Z", "updated_at": "2026-08-31T07:40:00.000000Z" }
  ]
}
```

> Hentikan server dengan `Ctrl + C` setelah selesai dipakai.

---

## 1. Apa itu OpenAPI?

**OpenAPI** adalah "bahasa standar" untuk mendeskripsikan sebuah API. Isinya menjawab 4 pertanyaan:

1. Endpoint apa saja yang tersedia?
2. Method-nya apa (`GET`, `POST`, `PUT`, `DELETE`)?
3. Butuh data apa saat request?
4. Mengembalikan data apa saat response?

Kalau file ini sudah ada, kita bisa membuat halaman dokumentasi interaktif (Swagger UI) yang bisa dipakai untuk mencoba API langsung dari browser.

> File OpenAPI boleh berformat `yaml` **atau** `json`. Panduan ini memakai **JSON** agar bisa langsung disimpan sebagai `openapi.json`.

---

## 2. Informasi Dasar API

| Hal | Nilai |
| --- | --- |
| Base URL | `http://praktikum-api.test` (server lokal Laravel Herd) |
| Prefix | `/api` (otomatis, karena route ada di `routes/api.php`) |
| Format data | JSON |
| Contoh lengkap | `http://praktikum-api.test/api/blogs` |

> Cek daftar route asli dengan: `php artisan route:list --path=api`

---

## 3. Anatomi File OpenAPI (Struktur Dasar)

File OpenAPI selalu punya 5 bagian utama, dengan urutan seperti ini:

```json
{
  "openapi": "3.0.3",
  "info": {},
  "servers": [],
  "paths": {},
  "components": {}
}
```

Artinya:

| Key | Isinya apa | Gunanya untuk apa |
| --- | --- | --- |
| `openapi` | Nomor versi aturan OpenAPI (`"3.0.3"`) | Memberi tahu tool (Swagger/Insomnia) aturan mana yang dipakai. Jangan diubah sembarangan. |
| `info` | Judul, deskripsi, versi API kita | "Kartu identitas" yang tampil di halaman dokumentasi. |
| `servers` | Daftar alamat server | Alamat dasar tempat API dijalankan. |
| `paths` | Daftar endpoint | Tempat mendaftarkan URL + method + request/response-nya. |
| `components` | Bentuk data (`schema`) yang dipakai ulang | Menghindari menulis bentuk data yang sama berulang kali. |

**Cara membaca panduan ini:** setiap langkah di bagian 4 hanya **menambah satu potongan** ke struktur di atas. Di bagian 5, semua potongan itu digabungkan menjadi satu file utuh.

---

## 4. Langkah-langkah Menulis Dokumentasi

### Langkah 1 — Tulis "kartu identitas" (`info` & `servers`)

```json
{
  "openapi": "3.0.3",
  "info": {
    "title": "Praktikum API - Blog",
    "description": "Dokumentasi API BREAD (Browse, Read, Edit, Add, Delete) untuk data Blog.",
    "version": "1.0.0"
  },
  "servers": [
    {
      "url": "http://praktikum-api.test/api",
      "description": "Server lokal"
    }
  ]
}
```

**Penjelasan tiap bagian:**

| Baris | Artinya |
| --- | --- |
| `"openapi": "3.0.3"` | Memakai aturan OpenAPI versi 3.0.3. |
| `info.title` | Nama API yang tampil sebagai judul di halaman dokumentasi. |
| `info.description` | Penjelasan singkat isi API. |
| `info.version` | Versi **aplikasi kita** (bukan versi OpenAPI). Naikkan angkanya kalau API berubah. |
| `servers` | Berupa **array** (`[ ]`), jadi boleh menampung lebih dari satu server (misal lokal, staging, produksi). |
| `servers[0].url` | Alamat dasar semua endpoint. Perhatikan: prefix `/api` sudah ditulis di sini, jadi di bagian `paths` nanti kita **tidak perlu** menulis `/api` lagi. |
| `servers[0].description` | Keterangan server, hanya untuk tampilan. |

### Langkah 2 — Definisikan bentuk data (`components.schemas`)

Model `Blog` diambil dari tabel `blogs`:

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| `id` | integer | Primary key |
| `title` | string | Judul blog (wajib) |
| `content` | string / null | Isi blog (boleh kosong) |
| `created_at` | date-time | Waktu dibuat |
| `updated_at` | date-time | Waktu diubah |

```json
{
  "components": {
    "schemas": {
      "Blog": {
        "type": "object",
        "properties": {
          "id": { "type": "integer", "example": 1 },
          "title": { "type": "string", "example": "Belajar Laravel" },
          "content": { "type": "string", "nullable": true, "example": "Membuat REST API sederhana" },
          "created_at": { "type": "string", "format": "date-time" },
          "updated_at": { "type": "string", "format": "date-time" }
        }
      },
      "BlogInput": {
        "type": "object",
        "required": ["title"],
        "properties": {
          "title": { "type": "string", "example": "Belajar Laravel" },
          "content": { "type": "string", "example": "Membuat REST API sederhana" }
        }
      }
    }
  }
}
```

**Penjelasan tiap bagian:**

| Baris | Artinya |
| --- | --- |
| `components.schemas` | Tempat menyimpan semua "cetakan" (blueprint) bentuk data. |
| `"Blog"`, `"BlogInput"` | Nama cetakan. Nama ini bebas, tapi nanti dipanggil lewat `$ref`. |
| `type: "object"` | Menandakan bentuk datanya adalah objek `{ ... }`. |
| `properties` | Daftar kolom yang ada di dalam objek tersebut. |
| `"id": { "type": "integer" }` | Kolom `id` bertipe angka bulat. |
| `"title": { "type": "string" }` | Kolom `title` bertipe teks. |
| `nullable: true` pada `content` | Boleh bernilai `null`, karena kolom `content` di database dibuat `->nullable()`. |
| `format: "date-time"` | Menandakan teks berformat tanggal-waktu standar (Laravel mengirim format ISO 8601). |
| `example` | Contoh nilai; hanya muncul sebagai contoh di dokumentasi, tidak memengaruhi validasi. |
| `required: ["title"]` | Hanya di `BlogInput`: saat kirim data, `title` **wajib** ada; `content` opsional. |

**Apa itu `$ref`?**
`"$ref": "#/components/schemas/Blog"` artinya: **"pakai bentuk data `Blog` yang sudah didefinisikan di atas"**. Tanda `#` berarti "di dalam file ini sendiri". Jadi kita tulis bentuk data sekali, lalu dipakai berkali-kali.

### Langkah 3 — Dokumentasikan **B (Browse)**: `GET /blogs`

```json
{
  "paths": {
    "/blogs": {
      "get": {
        "tags": ["Blog"],
        "summary": "B (Browse) - Menampilkan semua blog",
        "responses": {
          "200": {
            "description": "Daftar blog berhasil ditampilkan",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "data": {
                      "type": "array",
                      "items": { "$ref": "#/components/schemas/Blog" }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}
```

**Penjelasan tiap bagian:**

| Baris | Artinya |
| --- | --- |
| `paths` | Tempat mendaftarkan semua endpoint. |
| `"/blogs"` | URL endpoint (tanpa `/api`, karena `/api` sudah ada di `servers.url`). |
| `get` | Method HTTP-nya `GET` (untuk mengambil data). |
| `tags: ["Blog"]` | Mengelompokkan endpoint ini ke grup "Blog" di halaman dokumentasi. |
| `summary` | Judul singkat yang tampil pada endpoint. |
| `responses` | Daftar kemungkinan balasan dari server. |
| `"200"` | Balasan saat **berhasil** (kode status HTTP 200 OK). |
| `content."application/json".schema` | Menjelaskan bentuk JSON yang dikembalikan. |
| `data: { "type": "array" }` | Hasilnya array, sesuai kode route yang membungkus `Blog::get()` ke dalam key `data`. |
| `items: { "$ref": ... }` | Setiap elemen di dalam array mengikuti bentuk data `Blog`. |

**Contoh request:**

```bash
curl http://praktikum-api.test/api/blogs
```

**Contoh response:**

```json
{
  "data": [
    { "id": 1, "title": "Belajar Laravel", "content": "REST API sederhana", "created_at": "2026-08-31T07:40:00.000000Z", "updated_at": "2026-08-31T07:40:00.000000Z" }
  ]
}
```

### Langkah 4 — Dokumentasikan **R (Read)**: `GET /blog/{id}`

Endpoint ini butuh parameter di URL, yaitu `id`.

```json
{
  "paths": {
    "/blog/{id}": {
      "parameters": [
        {
          "name": "id",
          "in": "path",
          "required": true,
          "description": "ID blog",
          "schema": { "type": "integer" },
          "example": 1
        }
      ],
      "get": {
        "tags": ["Blog"],
        "summary": "R (Read) - Menampilkan detail blog berdasarkan id",
        "responses": {
          "200": {
            "description": "Detail blog berhasil ditampilkan",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "message": { "type": "string" },
                    "data": { "$ref": "#/components/schemas/Blog" }
                  }
                }
              }
            }
          }
        }
      }
    }
  }
}
```

**Penjelasan tiap bagian:**

| Baris | Artinya |
| --- | --- |
| `"/blog/{id}"` | URL dengan penanda `{id}`. Tanda kurung kurawal berarti bagian itu **diisi nilai variabel**, bukan ditulis apa adanya. |
| `parameters` | Daftar input yang dibutuhkan endpoint. Ditulis di level path, jadi otomatis berlaku untuk semua method di bawahnya. |
| `in: "path"` | Menandakan `id` berada **di dalam URL**, bukan di query string (`?id=1`). |
| `required: true` | Parameter ini wajib ada; kalau tidak ada, endpoint tidak bisa diakses. |
| `schema: { "type": "integer" }` | Nilai `id` harus berupa angka. |
| `responses."200"` | Balasan sukses, berisi `message` + `data`. |

**Contoh request:** `curl http://praktikum-api.test/api/blog/1`

**Contoh response:**

```json
{
  "message": "Detail blog berhasil ditampilkan",
  "data": { "id": 1, "title": "Belajar Laravel", "content": "REST API sederhana" }
}
```

### Langkah 5 — Dokumentasikan **A (Add)**: `POST /blogs`

Blok `post` ini ditulis **di dalam objek `"/blogs"`**, sejajar (satu tingkat) dengan `get` dari Langkah 3.

```json
{
  "post": {
    "tags": ["Blog"],
    "summary": "A (Add) - Menambahkan blog baru",
    "requestBody": {
      "required": true,
      "content": {
        "application/json": {
          "schema": { "$ref": "#/components/schemas/BlogInput" }
        }
      }
    },
    "responses": {
      "200": {
        "description": "Blog berhasil ditambahkan",
        "content": {
          "application/json": {
            "schema": {
              "type": "object",
              "properties": {
                "message": { "type": "string" },
                "data": { "$ref": "#/components/schemas/Blog" }
              }
            }
          }
        }
      }
    }
  }
}
```

**Penjelasan tiap bagian:**

| Baris | Artinya |
| --- | --- |
| `post` | Method HTTP untuk **membuat** data baru. |
| `requestBody` | Bagian yang menjelaskan **data yang harus dikirim** oleh pengirim request (hal yang tidak ada di GET). |
| `requestBody.required: true` | Request wajib menyertakan body; tidak boleh kosong. |
| `content."application/json"` | Body dikirim dalam format JSON. |
| `schema: { "$ref": ... "BlogInput" }` | Body harus mengikuti cetakan `BlogInput` (wajib ada `title`). |
| `responses."200"` | Mengembalikan pesan sukses + data blog yang baru dibuat. |

**Contoh request:**

```bash
curl -X POST http://praktikum-api.test/api/blogs \
  -H "Content-Type: application/json" \
  -d '{"title": "Belajar Laravel", "content": "REST API sederhana"}'
```

### Langkah 6 — Dokumentasikan **E (Edit)**: `PUT /blog/{id}`

Blok `put` ini ditulis **di dalam objek `"/blog/{id}"`**, sejajar dengan `get` dari Langkah 4. Parameter `id` tidak perlu ditulis ulang karena sudah diwarisi dari level path.

```json
{
  "put": {
    "tags": ["Blog"],
    "summary": "E (Edit) - Mengubah blog berdasarkan id",
    "requestBody": {
      "required": true,
      "content": {
        "application/json": {
          "schema": { "$ref": "#/components/schemas/BlogInput" }
        }
      }
    },
    "responses": {
      "200": {
        "description": "Blog berhasil diubah",
        "content": {
          "application/json": {
            "schema": {
              "type": "object",
              "properties": {
                "message": { "type": "string", "example": "Blog berhasil diubah" }
              }
            }
          }
        }
      }
    }
  }
}
```

**Penjelasan tiap bagian:**

| Baris | Artinya |
| --- | --- |
| `put` | Method HTTP untuk **mengubah** data yang sudah ada. |
| `requestBody` | Sama seperti POST: data baru yang dikirim untuk menggantikan data lama. |
| `$ref` ke `BlogInput` | Body wajib berisi `title` (dan boleh `content`). |
| `responses."200"` | Hanya mengembalikan `message`, karena route memang tidak mengirim balik data blog. |

**Contoh request:**

```bash
curl -X PUT http://praktikum-api.test/api/blog/1 \
  -H "Content-Type: application/json" \
  -d '{"title": "Judul Baru", "content": "Isi baru"}'
```

### Langkah 7 — Dokumentasikan **D (Delete)**: `DELETE /blog/{id}`

Blok `delete` ini juga ditulis **di dalam objek `"/blog/{id}"`**, sejajar dengan `get` dan `put`.

```json
{
  "delete": {
    "tags": ["Blog"],
    "summary": "D (Delete) - Menghapus blog berdasarkan id",
    "responses": {
      "200": {
        "description": "Blog berhasil dihapus",
        "content": {
          "application/json": {
            "schema": {
              "type": "object",
              "properties": {
                "message": { "type": "string", "example": "Blog berhasil dihapus" }
              }
            }
          }
        }
      }
    }
  }
}
```

**Penjelasan tiap bagian:**

| Baris | Artinya |
| --- | --- |
| `delete` | Method HTTP untuk **menghapus** data. |
| Tidak ada `requestBody` | Karena penghapusan hanya butuh `id` di URL, tidak perlu mengirim data apa pun. |
| `responses."200"` | Mengembalikan `message` bahwa data berhasil dihapus. |

**Contoh request:** `curl -X DELETE http://praktikum-api.test/api/blog/1`

**Cara menggabungkan:** `"/blogs"` berisi dua method (`get`, `post`), sedangkan `"/blog/{id}"` berisi tiga method (`get`, `put`, `delete`). Hasil gabungannya ada di bagian 5.

---

## 5. Contoh Lengkap `openapi.json`

Simpan kode di bawah sebagai file `openapi.json` di root project, lalu tempel ke Swagger Editor (lihat bagian 6).

```json
{
  "openapi": "3.0.3",
  "info": {
    "title": "Praktikum API - Blog",
    "description": "Dokumentasi API BREAD (Browse, Read, Edit, Add, Delete) untuk data Blog.",
    "version": "1.0.0"
  },
  "servers": [
    {
      "url": "http://praktikum-api.test/api",
      "description": "Server lokal"
    }
  ],
  "tags": [
    { "name": "Blog", "description": "Operasi BREAD pada data blog" }
  ],
  "paths": {
    "/blogs": {
      "get": {
        "tags": ["Blog"],
        "summary": "B (Browse) - Menampilkan semua blog",
        "responses": {
          "200": {
            "description": "Daftar blog berhasil ditampilkan",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "data": {
                      "type": "array",
                      "items": { "$ref": "#/components/schemas/Blog" }
                    }
                  }
                }
              }
            }
          }
        }
      },
      "post": {
        "tags": ["Blog"],
        "summary": "A (Add) - Menambahkan blog baru",
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": { "$ref": "#/components/schemas/BlogInput" }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Blog berhasil ditambahkan",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "message": { "type": "string" },
                    "data": { "$ref": "#/components/schemas/Blog" }
                  }
                }
              }
            }
          }
        }
      }
    },
    "/blog/{id}": {
      "parameters": [
        {
          "name": "id",
          "in": "path",
          "required": true,
          "description": "ID blog",
          "schema": { "type": "integer" },
          "example": 1
        }
      ],
      "get": {
        "tags": ["Blog"],
        "summary": "R (Read) - Menampilkan detail blog berdasarkan id",
        "responses": {
          "200": {
            "description": "Detail blog berhasil ditampilkan",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "message": { "type": "string" },
                    "data": { "$ref": "#/components/schemas/Blog" }
                  }
                }
              }
            }
          }
        }
      },
      "put": {
        "tags": ["Blog"],
        "summary": "E (Edit) - Mengubah blog berdasarkan id",
        "requestBody": {
          "required": true,
          "content": {
            "application/json": {
              "schema": { "$ref": "#/components/schemas/BlogInput" }
            }
          }
        },
        "responses": {
          "200": {
            "description": "Blog berhasil diubah",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "message": { "type": "string" }
                  }
                }
              }
            }
          }
        }
      },
      "delete": {
        "tags": ["Blog"],
        "summary": "D (Delete) - Menghapus blog berdasarkan id",
        "responses": {
          "200": {
            "description": "Blog berhasil dihapus",
            "content": {
              "application/json": {
                "schema": {
                  "type": "object",
                  "properties": {
                    "message": { "type": "string" }
                  }
                }
              }
            }
          }
        }
      }
    }
  },
  "components": {
    "schemas": {
      "Blog": {
        "type": "object",
        "properties": {
          "id": { "type": "integer", "example": 1 },
          "title": { "type": "string", "example": "Belajar Laravel" },
          "content": { "type": "string", "nullable": true, "example": "Membuat REST API sederhana" },
          "created_at": { "type": "string", "format": "date-time" },
          "updated_at": { "type": "string", "format": "date-time" }
        }
      },
      "BlogInput": {
        "type": "object",
        "required": ["title"],
        "properties": {
          "title": { "type": "string", "example": "Belajar Laravel" },
          "content": { "type": "string", "example": "Membuat REST API sederhana" }
        }
      }
    }
  }
}
```

**Penjelasan struktur akhir** (peta cepat isi file):

| Bagian | Isi | Diambil dari langkah |
| --- | --- | --- |
| `openapi` + `info` + `servers` | Identitas & alamat API | Langkah 1 |
| `tags` | Nama grup "Blog" | Pelengkap Langkah 3 |
| `paths."/blogs"` | `get` (Browse) + `post` (Add) | Langkah 3 & 5 |
| `paths."/blog/{id}"` | `parameters` + `get` (Read) + `put` (Edit) + `delete` (Delete) | Langkah 4, 6 & 7 |
| `components.schemas` | `Blog`, `BlogInput` | Langkah 2 |

---

## 6. Cara Melihat & Mencoba Dokumentasi

1. Buka **https://editor.swagger.io**
2. Hapus isi editor, lalu tempel seluruh isi `openapi.json` (Swagger Editor menerima JSON maupun YAML)
3. Sisi kanan akan menampilkan dokumentasi interaktif
4. Klik **Try it out** untuk mencoba endpoint langsung

### Cara import ke Insomnia

1. Buka aplikasi **Insomnia**
2. Klik tombol **Import** (atau menu **Application → Import**)
3. Pilih tab **File**, lalu pilih file `openapi.json` yang sudah dibuat
4. Insomnia akan membaca semua endpoint dan otomatis mengelompokkannya ke dalam sebuah collection
5. Pilih salah satu request, klik **Send** untuk mencoba endpoint langsung dari Insomnia

> Base URL (`http://praktikum-api.test/api`) diambil otomatis dari bagian `servers` di file OpenAPI. Kalau belum ada, tambahkan lewat menu **Manage Environments** dengan nama `base_url`.

**Alternatif lain:**

- **VS Code** → install extension *OpenAPI (Swagger) Preview*

---

## 7. (Opsional) Buat Otomatis dengan Scramble

Kalau malas menulis manual, Laravel punya package **Scramble** yang bisa membaca route dan model lalu membuat dokumentasi otomatis:

```bash
composer require dedoc/scramble
```

Setelah itu dokumentasi otomatis tersedia di:

```
http://praktikum-api.test/docs/api
```

> Catatan: Scramble butuh route yang rapi (biasanya memakai Controller + FormRequest). Karena project ini masih memakai closure di `routes/api.php`, hasilnya mungkin kurang lengkap — makanya panduan manual di atas tetap berguna.

---

## 8. Ringkasan Endpoint

| Method | URL | Fungsi | Kode | Letak di `paths` |
| --- | --- | --- | --- | --- |
| GET | `/api/blogs` | Menampilkan semua blog | Browse | `/blogs` → `get` |
| GET | `/api/blog/{id}` | Menampilkan detail blog | Read | `/blog/{id}` → `get` |
| POST | `/api/blogs` | Menambahkan blog baru | Add | `/blogs` → `post` |
| PUT | `/api/blog/{id}` | Mengubah blog | Edit | `/blog/{id}` → `put` |
| DELETE | `/api/blog/{id}` | Menghapus blog | Delete | `/blog/{id}` → `delete` |
