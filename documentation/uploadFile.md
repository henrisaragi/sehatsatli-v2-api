Upload FIle : - Contoh dapat dilihat di UserForm.jsx userSlice.js UserController.php

Standard : kita gunakan nama yang akan di upload dengan variabel 'file'

1. Ubah di Form, validasi jadikan `mixed()` 

   ```javascript
   file: yup.mixed().nullable(),
   ```

2. Ubah di Form Input Menjadi sbb :

   ```javascript
   <InputLabels.Vertical
   	label="Gambar/Foto"
   	name="file"
   >
     <>
     {data?.media
      ? data.media.map((media) => (
        <img src={media.original_url} className="w-96" />
        ))
        : null}
        {data?.media && data?.media.length > 0 ? (
          <p className="text-xs">Upload gambar baru</p>
        ) : null}
   	</>
     <InputControllers.Image
       name="file"
       defaultValue={null}
       asBase64={false}
     />
   </InputLabels.Vertical>
   ```

3. Tambahkan logic after save di slice :

   ```javascript
   import {
     processData,
     processFetchList,
     processFetchOne,
     processInit,
     processDataFormData, // Jangan lupa ditambah disini
   } from "utilities/Networking";
   
   const UPLOAD_MEDIA = "uploadPhoto"; // Ini juga arahkan ke api
   
   ///...
   saveUserDetail: (data) => {
       let newData = {
         ...get().users?.current,
         ...data,
       };
   
   	  // disini, pisahkan file dari data yang mau dikirim
       let { file, ...dataInJson } = newData;
   
       return processData(
         { set, get },
         `${PREFIX}/${SAVE}`,
         "users",
         "POST",
         dataInJson // yang dikirim tanpa variabel file
       )
         .then((result) => {
         
         	// disini letak habis savingnya, cek sukses ga dan ada file ga
           console.log("update success", result);
           if (file && result.success) {
             console.log({ file });
             
             // disini letak habis savingnya, cek sukses ga dan ada file ga
             return processDataFormData(
               { set, get },
               `${PREFIX}/${UPLOAD_MEDIA}`, // Jangan lupa ditambah
               "users",
               "POST",
               { id: result.data.id, file: file } // buat struktur spt ini
             );
           }
           return result;
         })
         .then((result) => {
           get().fetchUserList();
           return result;
         });
     },
   ```

   

4. Server side : tambah di api.php

   ```php
   // Post yaaa! dan sesuaikan dgn prefixnya
   Route::post('users/uploadPhoto', [UserController::class, 'uploadFile']);
   ```

   

5. Model :

   ```php
   use Spatie\Activitylog\Traits\LogsActivity;
   use Spatie\Activitylog\LogOptions;
   
   // Import ini
   use Spatie\MediaLibrary\HasMedia;
   use Spatie\MediaLibrary\InteractsWithMedia;
   
   // Ini harus Ada implementasi HasMedia
   class User extends Authenticatable implements HasMedia
   {
       protected $table = "users";
       
       // Pastikan disini ada InteractsWithMedia
       use HasApiTokens, HasFactory, Notifiable,  LogsActivity, InteractsWithMedia;
   
   
   ```

   

6. Controller - Saving

```php
public function uploadFile(Request $request)
    {
        try {
            // Jangan lupa ada tarik id
            $id = $request->input('id');

          	// Sesuaikan dgn modelnya
            $user = User::with('media')
                ->where('status', 1)
                ->where('id', $id)
                ->first();

            // Ini karena cm mau simpan 1 foto aja
            foreach ($user->media as $id => $media) {
                $media->delete();
            }

	          // Disini jantungnya
            $user->addMediaFromRequest('file')
                ->toMediaCollection();

            $user = User::with('media')
                ->where('status', 1)
                ->where('id', $id)
                ->first();

            return [
                'success' => true,
                'data' => $user
            ];
        } catch (Exception $e) {
            Log::error($this->controllerName . '-uploadFile: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }
```

7. Controller - Get :

```php
public function getOne(Request $request)
    {
        try {
            $params = $request->post();

          	// Disini tambahkan with media
            $user = User::with('media')->where('status', 1)->where('id', $params['id'])->first();
            return [
                'success' => true,
                'data' => $user
            ];
        } catch (Exception $e) {
            Log::info($this->controllerName . '-getOne: success=false; error=' . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error, cannot load data"
            ];
        }
    }
```

