Tambahkan reset store di masing2 slice, contoh :

Di general report ada ini :

```javascript

export const generalReportSlice = (set, get) => ({
  generalReports: {
    list: null,
    current: null,
    loading: true,
    processing: true,
    processing_result: null,
  },
  fetchGeneralReportList: () => {
    return processFetchList(
      { set, get },
```

Kita reset generalReports-nya di satu function, caranya tambahkan :

```javascript
  ...
  resetGeneralReport: () => {
    set((state) => {
      state.generalReports = {
        list: null,
        current: null,
        loading: true,
        processing: true,
        processing_result: null,
      };
    });
  },
  deleteGeneralReport: (id) => {
    return processData(
    ...

```

Lakukan ini untuk semua slice.

Di authSlice.js panggil function2 reset tersebut di function logout :

```javascript
  signOut: () => {
    let store = get();
    set((state) => {
      state.auths.current = null;
      state.auths.valid = false;
    });

    store.resetGeneralReport();
    ...
  },
```
