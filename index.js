const express = require('express');
const crypto = require('crypto');

const app = express();
const port = process.env.PORT || 3000;

app.use(express.json());

app.get('/', (req, res) => {
  res.json({
    status: 'HMAC Service running',
    endpoints: {
      'POST /hmac': 'Generate HMAC',
      'GET /': 'Health check'
    },
    timestamp: Math.floor(Date.now() / 1000)
  });
});

app.post('/hmac', (req, res) => {
  try {
    const { data, secret } = req.body;
    
    if (!data || !secret) {
      return res.status(400).json({
        error: 'data and secret required'
      });
    }
    
    const hmac = crypto.createHmac('sha256', secret).update(data).digest('base64');
    
    res.json({
      hmac: hmac,
      success: true,
      timestamp: Math.floor(Date.now() / 1000)
    });
    
  } catch (error) {
    res.status(500).json({
      error: error.message,
      success: false
    });
  }
});

app.listen(port, () => {
  console.log(`HMAC Service läuft auf Port ${port}`);
});
