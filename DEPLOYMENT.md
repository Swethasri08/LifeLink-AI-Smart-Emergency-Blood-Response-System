# 🚀 Vercel Deployment Guide

This guide will help you deploy the Blood Donation Management System to Vercel for live hosting.

## 📋 Prerequisites

1. **Vercel Account**: Sign up at [vercel.com](https://vercel.com)
2. **Vercel CLI**: Install Vercel CLI
3. **GitHub Repository**: Your code should be on GitHub
4. **Database**: External MySQL database (recommended)

## 🛠️ Installation Steps

### Step 1: Install Vercel CLI
```bash
# Using npm
npm i -g vercel

# Using yarn
yarn global add vercel
```

### Step 2: Login to Vercel
```bash
vercel login
```

### Step 3: Deploy to Vercel
```bash
cd /path/to/your/project
vercel --prod
```

## 🗄️ Database Setup Options

### Option 1: External MySQL Database (Recommended)
1. Get a free MySQL database from:
   - [PlanetScale](https://planetscale.com/)
   - [Railway](https://railway.app/)
   - [Aiven](https://aiven.io/)
   - [DigitalOcean](https://www.digitalocean.com/products/managed-databases)

2. Get the connection string (DATABASE_URL)
3. Add to Vercel environment variables

### Option 2: Vercel Postgres (Free Tier)
1. Convert MySQL to PostgreSQL
2. Modify database connection code
3. Use Vercel's free Postgres database

### Option 3: SQLite (Development Only)
1. Convert to SQLite for simple deployment
2. File-based database (not recommended for production)

## 🔧 Environment Variables

Set these in Vercel dashboard:

### Database Connection
```
DATABASE_URL=mysql://username:password@host:port/database_name
DB_HOST=your_database_host
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_NAME=bdms
```

### Application
```
APP_ENV=production
```

## 📁 Project Structure for Vercel

```
bdms/
├── index.php              # Entry point
├── config/
│   └── database.php      # Database configuration
├── screenshots/             # Application screenshots
├── vendor/                 # Composer dependencies
├── *.php                  # All PHP files
├── *.css                  # Stylesheets
├── *.js                   # JavaScript files
├── vercel.json            # Vercel configuration
├── package.json            # Node.js configuration
└── README.md               # Documentation
```

## 🚀 Deployment Commands

### Automatic Deployment
```bash
# Deploy to production
vercel --prod

# Deploy to preview
vercel
```

### Custom Domain
```bash
# Deploy with custom domain
vercel --prod --domain yourdomain.com
```

## 🔍 Troubleshooting

### Common Issues

#### 1. Database Connection Errors
**Problem**: "Database connection failed"
**Solution**:
- Check DATABASE_URL environment variable
- Verify database credentials
- Ensure database is accessible from Vercel

#### 2. PHP Version Issues
**Problem**: "PHP version not supported"
**Solution**:
- Update vercel.json PHP runtime
- Use `@vercel/php` builder
- Check PHP version compatibility

#### 3. File Not Found
**Problem**: "404 errors"
**Solution**:
- Check vercel.json routes configuration
- Verify file paths
- Check build output

#### 4. Permission Issues
**Problem**: "Permission denied"
**Solution**:
- Check file permissions
- Verify database user permissions
- Check Vercel environment variables

## 📊 Performance Optimization

### For Production
1. **Enable caching** in Vercel dashboard
2. **Use CDN** for static assets
3. **Optimize images** and files
4. **Monitor performance** with Vercel Analytics

### Database Optimization
1. **Use connection pooling**
2. **Optimize queries**
3. **Add proper indexes**
4. **Regular backups**

## 🔒 Security Considerations

### Vercel Security
1. **HTTPS**: Automatic SSL certificates
2. **Environment variables**: Secure sensitive data
3. **Access control**: Limit database access
4. **Monitoring**: Set up alerts

### Application Security
1. **Input validation**: Sanitize all inputs
2. **SQL injection**: Use prepared statements
3. **Session security**: Secure session handling
4. **Error handling**: Don't expose sensitive info

## 📈 Scaling

### Vertical Scaling
- **Database**: Upgrade database plan
- **Server**: Increase serverless function limits
- **CDN**: Enable global distribution

### Horizontal Scaling
- **Load balancing**: Multiple deployments
- **Geographic distribution**: Edge locations
- **Caching**: Redis integration

## 💰 Cost Optimization

### Vercel Free Tier
- **100GB bandwidth** per month
- **Serverless functions**: Free tier limits
- **Custom domains**: 1 free domain

### Database Costs
- **Shared hosting**: $5-20/month
- **Managed database**: $10-50/month
- **Connection pooling**: Optimize connections

## 🔄 CI/CD Pipeline

### GitHub Actions
```yaml
name: Deploy to Vercel
on:
  push:
    branches: [master]
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - uses: amondnet/vercel-action@v20
        with:
          vercel-token: ${{ secrets.VERCEL_TOKEN }}
          vercel-org-id: ${{ secrets.VERCEL_ORG_ID }}
          vercel-project-id: ${{ secrets.VERCEL_PROJECT_ID }}
```

## 📞 Support

### Vercel Documentation
- [Vercel Docs](https://vercel.com/docs)
- [PHP on Vercel](https://vercel.com/docs/concepts/functions/serverless-functions)
- [Environment Variables](https://vercel.com/docs/concepts/projects/environment-variables)

### Database Providers
- [PlanetScale MySQL](https://planetscale.com/docs)
- [Railway MySQL](https://docs.railway.app/database/mysql)
- [Aiven MySQL](https://aiven.io/docs/products/mysql/)

---

## 🎯 Quick Start

1. **Install Vercel CLI**
   ```bash
   npm i -g vercel
   ```

2. **Login to Vercel**
   ```bash
   vercel login
   ```

3. **Deploy Your Project**
   ```bash
   cd C:/xampp/htdocs/bdms
   vercel --prod
   ```

4. **Configure Database**
   - Add DATABASE_URL to Vercel environment variables
   - Test connection

5. **Access Your Live Site**
   - Your site will be available at `https://your-project.vercel.app`

---

**🎉 Your Blood Donation Management System will be live on Vercel!**
