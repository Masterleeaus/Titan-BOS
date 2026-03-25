/* Titan Zero BOS IndexedDB bootstrap */

const DB_NAME = 'titan-zero-bos';
const DB_VERSION = 2;
const STORES = [
  'tz_jobs',
  'tz_customers',
  'tz_invoices',
  'tz_local_signals',
  'tz_sync_queue',
  'tz_runtime_meta',
];

export function initDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, DB_VERSION);

    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      STORES.forEach((store) => {
        if (!db.objectStoreNames.contains(store)) {
          db.createObjectStore(store, { keyPath: 'id', autoIncrement: true });
        }
      });
    };

    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

const withStore = async (storeName, mode, callback) => {
  const db = await initDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction(storeName, mode);
    const store = tx.objectStore(storeName);
    callback(store, resolve, reject);
    tx.onerror = () => reject(tx.error);
  });
};

export async function putRecord(storeName, record) {
  return withStore(storeName, 'readwrite', (store, resolve) => {
    store.put({ ...record, updated_at: Date.now() });
    resolve(true);
  });
}

export async function getRecord(storeName, key) {
  return withStore(storeName, 'readonly', (store, resolve) => {
    const req = store.get(key);
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => resolve(null);
  });
}

export async function queueSignal(signal) {
  return withStore('tz_local_signals', 'readwrite', (store, resolve) => {
    store.add({
      ...signal,
      status: 'pending',
      queued_at: Date.now(),
    });
    resolve(true);
  });
}

export async function flushSignals(limit = 50) {
  const pending = await readPendingSignals(limit);

  await Promise.all(
    pending.map((signal) =>
      withStore('tz_sync_queue', 'readwrite', (store, resolve) => {
        store.add({
          object_type: signal.signal_type ?? 'signal',
          object_id: signal.id ?? null,
          action: signal.action ?? 'capture',
          status: 'pending',
          retry_count: 0,
          payload_json: signal.payload_json ?? signal,
          created_at: Date.now(),
        });
        resolve(true);
      }),
    ),
  );

  await Promise.all(
    pending.map((signal) =>
      withStore('tz_local_signals', 'readwrite', (store, resolve) => {
        store.put({ ...signal, status: 'queued', updated_at: Date.now() });
        resolve(true);
      }),
    ),
  );

  return pending.length;
}

async function readPendingSignals(limit = 50) {
  const db = await initDB();
  return new Promise((resolve, reject) => {
    const tx = db.transaction('tz_local_signals', 'readonly');
    const store = tx.objectStore('tz_local_signals');
    const results = [];
    const cursorReq = store.openCursor();
    cursorReq.onsuccess = (event) => {
      const cursor = event.target.result;
      if (cursor && results.length < limit && cursor.value.status === 'pending') {
        results.push(cursor.value);
        cursor.continue();
      } else {
        resolve(results);
      }
    };
    cursorReq.onerror = () => reject(cursorReq.error);
  });
}
