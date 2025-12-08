const express = require('express');
const { getDb } = require('./db');

const app = express();
const db = getDb();
const PORT = process.env.PORT || 3000;

app.use(express.json());

app.post('/records', (req, res) => {
  const { title, content } = req.body || {};

  if (!title || !content) {
    return res.status(400).json({ error: 'Both title and content are required' });
  }

  const insertQuery = 'INSERT INTO records (title, content) VALUES (?, ?)';
  db.run(insertQuery, [title, content], function onInsert(err) {
    if (err) {
      console.error('Failed to insert record', err);
      return res.status(500).json({ error: 'Failed to create record' });
    }

    db.get('SELECT * FROM records WHERE id = ?', [this.lastID], (getErr, row) => {
      if (getErr || !row) {
        console.error('Failed to load new record', getErr);
        return res.status(500).json({ error: 'Failed to load created record' });
      }

      return res.status(201).json(row);
    });
  });
});

app.get('/records', (_req, res) => {
  db.all('SELECT * FROM records ORDER BY created_at DESC', (err, rows) => {
    if (err) {
      console.error('Failed to fetch records', err);
      return res.status(500).json({ error: 'Failed to fetch records' });
    }

    return res.json(rows);
  });
});

app.get('/records/:id', (req, res) => {
  const recordId = Number.parseInt(req.params.id, 10);

  if (Number.isNaN(recordId)) {
    return res.status(400).json({ error: 'Record id must be a number' });
  }

  db.get('SELECT * FROM records WHERE id = ?', [recordId], (err, row) => {
    if (err) {
      console.error('Failed to fetch record', err);
      return res.status(500).json({ error: 'Failed to fetch record' });
    }

    if (!row) {
      return res.status(404).json({ error: 'Record not found' });
    }

    return res.json(row);
  });
});

app.listen(PORT, () => {
  console.log(`Server is running on http://localhost:${PORT}`);
});

