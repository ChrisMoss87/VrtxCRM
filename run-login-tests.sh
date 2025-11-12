#!/bin/bash

# VrtxCRM Login Test Runner
# This script sets up and runs Playwright login tests

set -e

echo "========================================="
echo "VrtxCRM Login Test Setup & Runner"
echo "========================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if domain is in /etc/hosts
echo "1. Checking /etc/hosts configuration..."
if grep -q "acme.vrtxcrm.local" /etc/hosts; then
    echo -e "${GREEN}✓${NC} Domain acme.vrtxcrm.local is configured in /etc/hosts"
else
    echo -e "${RED}✗${NC} Domain not found in /etc/hosts"
    echo ""
    echo "Please add the domain:"
    echo -e "${YELLOW}echo '127.0.0.1 acme.vrtxcrm.local' | sudo tee -a /etc/hosts${NC}"
    echo ""
    exit 1
fi

# Check if tenant exists
echo ""
echo "2. Checking tenant database..."
TENANT_CHECK=$(php artisan tinker --execute="
use App\Models\Tenancy\Tenant;
\$tenant = Tenant::first();
echo \$tenant ? 'exists' : 'missing';
")

if [[ "$TENANT_CHECK" == *"exists"* ]]; then
    echo -e "${GREEN}✓${NC} Tenant database exists"
else
    echo -e "${RED}✗${NC} No tenant found"
    echo "Creating test tenant..."
    php artisan tinker --execute="
        \$tenant = app(App\Services\TenantService::class)->createTenant([
            'name' => 'Acme Corporation',
            'subdomain' => 'acme',
            'plan' => 'professional',
            'email' => 'admin@test.com',
        ]);
        echo \"✓ Tenant created: {\$tenant->id}\n\";
    "
fi

# Check if Playwright browsers are installed
echo ""
echo "3. Checking Playwright browsers..."
if npx playwright --version > /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Playwright is installed"

    # Install browsers if needed
    if [ ! -d "$HOME/.cache/ms-playwright" ]; then
        echo "Installing Playwright browsers..."
        npx playwright install chromium
    else
        echo -e "${GREEN}✓${NC} Playwright browsers are installed"
    fi
else
    echo -e "${RED}✗${NC} Playwright not found"
    exit 1
fi

# Check if dev server is running
echo ""
echo "4. Checking development server..."
if curl -s http://localhost:5173 > /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Vite dev server is running"
else
    echo -e "${YELLOW}⚠${NC} Vite dev server not detected"
    echo ""
    echo "Please start the dev server in another terminal:"
    echo -e "${YELLOW}npm run dev${NC}"
    echo ""
    read -p "Press Enter when server is ready, or Ctrl+C to exit..."
fi

# Ask which tests to run
echo ""
echo "========================================="
echo "Select tests to run:"
echo "========================================="
echo "1. All login tests"
echo "2. Basic login tests only"
echo "3. Comprehensive login tests"
echo "4. UI Mode (interactive)"
echo "5. Debug Mode"
echo ""
read -p "Enter choice (1-5): " choice

case $choice in
    1)
        echo ""
        echo "Running all login tests..."
        npx playwright test tests/browser/login*.spec.ts tests/browser/auth.spec.ts
        ;;
    2)
        echo ""
        echo "Running basic login tests..."
        npx playwright test tests/browser/login-test.spec.ts
        ;;
    3)
        echo ""
        echo "Running comprehensive login tests..."
        npx playwright test tests/browser/login.comprehensive.spec.ts
        ;;
    4)
        echo ""
        echo "Starting UI Mode..."
        npx playwright test --ui
        ;;
    5)
        echo ""
        echo "Starting Debug Mode..."
        npx playwright test --debug tests/browser/login-test.spec.ts
        ;;
    *)
        echo -e "${RED}Invalid choice${NC}"
        exit 1
        ;;
esac

# Show results
echo ""
echo "========================================="
echo "Test Results"
echo "========================================="
echo ""
echo "View HTML report: npx playwright show-report"
echo "Test results saved to: test-results/"
echo ""
