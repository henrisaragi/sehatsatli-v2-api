Split komponen yang ada di dashboard

Contoh di : `<LabResultList />`

```javascript
...
   <div className="w-full px-3 uppercase">
            Jumlah Kasus Suspek Non Penyakit
        </div>
        <GraphNonDisease />
        </div>
    </div>

    <LabResultList /> --> self contained

    <div className="w-full bg-white">
        <div className="my-2 mx-3 uppercase">Kejadian Suspek Penyakit</div>
        <DiseaseReportList {...params_disease} />
    </div>
    <div className="w-full bg-white">
        <div className="my-2 mx-3 uppercase">
...
```

Semua judul di pindah, di wrap pakai Card, tambah classname utk adjust spacing

```javascript
import Card from "components/Cards/Card";
...

<Card title="Lab Result" className="my-2.5">
    <TableSyncfusion
    allowFiltering={true}
    allowSearch={true}
    data={data_tabel?.list ?? []} // Cek disini pakai ? default ke []
    columns={columns}
    />
</Card>
```

Pindahkan semua useeffect dan use-store-nya ke komponen yang bersangkutan

```javascript
const [data_tabel, fetchList] = useDashboard();

useEffect(() => {
    fetchList();
}, []);
```
