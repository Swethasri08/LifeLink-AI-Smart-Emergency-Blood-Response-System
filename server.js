const express = require('express');
const path = require('path');
const fs = require('fs');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname)));

// Serve static files (CSS, JS, images)
app.use('/css', express.static(path.join(__dirname, 'css')));
app.use('/js', express.static(path.join(__dirname, 'js')));
app.use('/images', express.static(path.join(__dirname, 'images')));
app.use('/screenshots', express.static(path.join(__dirname, 'screenshots')));

// Database configuration (for connection info)
const dbConfig = {
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USERNAME || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'bdms'
};

// Main route - serve the PHP application
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'index.php'));
});

// Handle all PHP files
app.get('*.php', (req, res) => {
  const filePath = path.join(__dirname, req.path);
  
  if (fs.existsSync(filePath)) {
    // For demo purposes, we'll serve PHP files as plain text
    // In production, you'd need a PHP interpreter
    res.sendFile(filePath);
  } else {
    res.status(404).send('File not found');
  }
});

// Handle POST requests to PHP files
app.post('*.php', (req, res) => {
  const filePath = path.join(__dirname, req.path);
  
  if (fs.existsSync(filePath)) {
    // For demo purposes, we'll simulate PHP processing
    // In production, you'd need actual PHP execution
    
    // Read the PHP file
    fs.readFile(filePath, 'utf8', (err, data) => {
      if (err) {
        res.status(500).send('Server error');
        return;
      }
      
      // Simple simulation of PHP form processing
      if (req.path.includes('login_process.php')) {
        // Simulate login response
        if (req.body.email && req.body.password) {
          res.send(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Login Success</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body>
                <div class="container mt-5">
                    <div class="alert alert-success">
                        <h4>Login Successful!</h4>
                        <p>Welcome, ${req.body.email}</p>
                        <p>This is a demo version. The full PHP application requires a PHP server environment.</p>
                        <a href="/" class="btn btn-primary">Back to Login</a>
                    </div>
                </div>
            </body>
            </html>
          `);
        } else {
          res.send(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Login Failed</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body>
                <div class="container mt-5">
                    <div class="alert alert-danger">
                        <h4>Login Failed!</h4>
                        <p>Please check your credentials.</p>
                        <a href="/" class="btn btn-primary">Back to Login</a>
                    </div>
                </div>
            </body>
            </html>
          `);
        }
      } else if (req.path.includes('register.php')) {
        // Simulate registration
        res.send(`
          <!DOCTYPE html>
          <html>
          <head>
              <title>Registration Success</title>
              <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
          </head>
          <body>
              <div class="container mt-5">
                  <div class="alert alert-success">
                      <h4>Registration Successful!</h4>
                      <p>Your account has been created.</p>
                      <p>This is a demo version. The full PHP application requires a PHP server environment.</p>
                      <a href="/" class="btn btn-primary">Back to Login</a>
                  </div>
              </div>
          </body>
          </html>
        `);
      } else {
        // For other PHP files, just return the file content
        res.sendFile(filePath);
      }
    });
  } else {
    res.status(404).send('File not found');
  }
});

// API endpoint for database info
app.get('/api/db-info', (req, res) => {
  res.json({
    message: 'Database configuration',
    config: {
      host: dbConfig.host,
      database: dbConfig.database,
      user: dbConfig.user,
      connected: false // In demo mode
    }
  });
});

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({ 
    status: 'ok', 
    message: 'Blood Donation Management System Demo',
    timestamp: new Date().toISOString()
  });
});

// Start server
app.listen(PORT, () => {
  console.log(`Blood Donation Management System running on port ${PORT}`);
  console.log(`Access your application at: http://localhost:${PORT}`);
  console.log(`Note: This is a demo version. Full PHP functionality requires a PHP server.`);
});

module.exports = app;
