const { MongoClient } = require('mongodb');

let cachedClient = null;

async function getCollection() {
  const uri = process.env.MONGO_URI;
  if (!uri) {
    throw new Error('MONGO_URI manquant');
  }
  if (!cachedClient) {
    cachedClient = new MongoClient(uri);
    await cachedClient.connect();
  }
  const dbName = process.env.MONGO_DATABASE || 'vite_et_gourmand';
  const colName = process.env.MONGO_COLLECTION || 'commandes_stats';
  return cachedClient.db(dbName).collection(colName);
}

function authorize(req) {
  const key = process.env.INSTALL_KEY || 'vitegourmand2026';
  const header = req.headers['x-mongo-key'] || req.headers['X-Mongo-Key'];
  const queryKey = req.query && req.query.key;
  return header === key || queryKey === key;
}

module.exports = async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type, x-mongo-key');

  if (req.method === 'OPTIONS') {
    return res.status(204).end();
  }

  if (!authorize(req)) {
    return res.status(403).json({ error: 'Forbidden' });
  }

  try {
    const col = await getCollection();

    if (req.method === 'POST') {
      const body = typeof req.body === 'string' ? JSON.parse(req.body || '{}') : (req.body || {});
      const doc = {
        id_commande: Number(body.id_commande),
        id_menu: Number(body.id_menu),
        menu_titre: String(body.menu_titre || ''),
        montant: Number(body.montant || 0),
        date_commande: new Date(),
      };
      await col.insertOne(doc);
      return res.status(201).json({ ok: true });
    }

    if (req.method === 'GET') {
      const filter = {};
      if (req.query.menu) {
        filter.id_menu = Number(req.query.menu);
      }
      const docs = await col.find(filter).toArray();
      const mapped = docs.map((d) => ({
        id_commande: d.id_commande,
        id_menu: d.id_menu,
        menu_titre: d.menu_titre,
        montant: d.montant,
        date_commande: d.date_commande ? new Date(d.date_commande).toISOString().slice(0, 10) : null,
      }));
      return res.status(200).json(mapped);
    }

    return res.status(405).json({ error: 'Method not allowed' });
  } catch (e) {
    console.error(e);
    return res.status(500).json({ error: e.message || 'Mongo error' });
  }
};
