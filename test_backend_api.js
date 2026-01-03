#!/usr/bin/env node

/**
 * Backend API Test Script
 * Tests the actual API endpoints to ensure addons are being returned correctly
 *
 * Usage: node test_backend_api.js
 */

import http from 'http';

const BASE_URL = 'http://localhost:8000';
const API_BASE = BASE_URL + '/api/customer/menu';

// Note: This is a simplified test - in real usage, you'd need proper authentication
// For now, this just tests the API structure and response format

function makeRequest(url) {
    return new Promise((resolve, reject) => {
        const urlObj = new URL(url);
        const options = {
            hostname: urlObj.hostname,
            port: urlObj.port,
            path: urlObj.pathname + urlObj.search,
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };

        const req = http.request(options, (res) => {
            let data = '';
            res.on('data', (chunk) => {
                data += chunk;
            });
            res.on('end', () => {
                try {
                    const jsonData = JSON.parse(data);
                    resolve({
                        status: res.statusCode,
                        data: jsonData
                    });
                } catch (e) {
                    resolve({
                        status: res.statusCode,
                        data: data
                    });
                }
            });
        });

        req.on('error', (err) => {
            reject(err);
        });

        req.end();
    });
}

async function testVendorsEndpoint() {
    console.log('\n🔍 Testing /vendors endpoint...');
    try {
        const response = await makeRequest(API_BASE + '/vendors');
        console.log(`Status: ${response.status}`);
        if (response.data.success && response.data.vendors) {
            console.log(`✅ Found ${response.data.vendors.length} vendors`);
            return response.data.vendors;
        } else {
            console.log('❌ Failed to get vendors:', response.data);
            return [];
        }
    } catch (error) {
        console.log('❌ Error fetching vendors:', error.message);
        return [];
    }
}

async function testVendorProducts(vendorId) {
    console.log(`\n🔍 Testing /vendors/${vendorId}/products endpoint...`);
    try {
        const response = await makeRequest(API_BASE + `/vendors/${vendorId}/products`);
        console.log(`Status: ${response.status}`);

        if (response.data.success && response.data.products) {
            const products = response.data.products;
            console.log(`✅ Found ${products.length} products`);

            // Check if products have addons
            let productsWithAddons = 0;
            products.forEach((product, index) => {
                const hasAddons = product.addons && Array.isArray(product.addons);
                if (hasAddons) {
                    productsWithAddons++;
                    console.log(`  Product ${index + 1}: ${product.name} - ${product.addons.length} addons`);
                } else {
                    console.log(`  Product ${index + 1}: ${product.name} - No addons`);
                }
            });

            console.log(`📊 Summary: ${productsWithAddons}/${products.length} products have addons`);

            // Return a product with addons for testing
            return products.find(p => p.addons && p.addons.length > 0) || products[0];
        } else {
            console.log('❌ Failed to get products:', response.data);
            return null;
        }
    } catch (error) {
        console.log('❌ Error fetching products:', error.message);
        return null;
    }
}

async function testProductDetails(productId) {
    console.log(`\n🔍 Testing /products/${productId} endpoint...`);
    try {
        const response = await makeRequest(API_BASE + `/products/${productId}`);
        console.log(`Status: ${response.status}`);

        if (response.data.success && response.data.product) {
            const product = response.data.product;
            console.log(`✅ Got product details: ${product.name}`);

            if (product.addons && Array.isArray(product.addons)) {
                console.log(`✅ Product has ${product.addons.length} addons:`);
                product.addons.forEach((addon, index) => {
                    console.log(`  Addon ${index + 1}: ${addon.name} - ₱${addon.price} (active: ${addon.is_active})`);
                });
            } else {
                console.log('❌ Product has no addons or addons is not an array');
            }

            return product;
        } else {
            console.log('❌ Failed to get product details:', response.data);
            return null;
        }
    } catch (error) {
        console.log('❌ Error fetching product details:', error.message);
        return null;
    }
}

async function runTests() {
    console.log('🧪 Starting Backend API Tests');
    console.log('='.repeat(50));

    console.log('\n⚠️  Note: These tests require the Laravel server to be running on localhost:8000');
    console.log('Start with: php artisan serve');

    try {
        // Test 1: Get vendors
        const vendors = await testVendorsEndpoint();

        if (vendors.length === 0) {
            console.log('\n❌ No vendors found. Cannot proceed with tests.');
            console.log('💡 Make sure:');
            console.log('   1. Laravel server is running (php artisan serve)');
            console.log('   2. Database has vendor data');
            console.log('   3. Vendors have is_active = true');
            return;
        }

        // Test 2: Get products for first vendor
        const vendor = vendors[0];
        const product = await testVendorProducts(vendor.id);

        if (!product) {
            console.log('\n❌ No products found for vendor. Cannot proceed with tests.');
            console.log('💡 Make sure:');
            console.log('   1. Vendor has products');
            console.log('   2. Products have is_active = true');
            return;
        }

        // Test 3: Get product details
        const productDetails = await testProductDetails(product.id);

        // Summary
        console.log('\n📊 Test Summary:');
        console.log('='.repeat(30));

        if (product.addons && product.addons.length > 0) {
            console.log('✅ Products endpoint returns addons correctly');
        } else {
            console.log('❌ Products endpoint does not return addons');
        }

        if (productDetails && productDetails.addons && productDetails.addons.length > 0) {
            console.log('✅ Product details endpoint returns addons correctly');
        } else {
            console.log('❌ Product details endpoint does not return addons');
        }

        console.log('\n🎯 Next Steps:');
        console.log('1. If all tests pass ✅, the backend is working correctly');
        console.log('2. If tests fail ❌, check database and backend code');
        console.log('3. Test the frontend using test_addons_frontend.html');

    } catch (error) {
        console.log('\n❌ Test failed with error:', error.message);
        console.log('\n💡 Common issues:');
        console.log('   1. Laravel server not running');
        console.log('   2. Database connection issues');
        console.log('   3. Authentication required for API calls');
    }
}

// Run tests if this script is executed directly
if (import.meta.url === `file://${process.argv[1]}`) {
    runTests();
}

export {
    makeRequest,
    testVendorsEndpoint,
    testVendorProducts,
    testProductDetails,
    runTests
};
