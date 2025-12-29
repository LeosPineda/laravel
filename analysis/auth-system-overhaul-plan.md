# Authentication System Overhaul Plan

## 🎯 **CURRENT ISSUES IDENTIFIED:**

### **1. 419 CSRF Login Errors**
```
POST http://127.0.0.1:8000/login 419 (unknown status)
```

### **2. Wrong User Registration Flow**
- Currently: All users can register as any role
- Should be: Only customers register normally
- Should be: Superadmin creates vendor accounts

## 📋 **REQUIRED WORKFLOW:**

### **User Creation Process:**
1. **Superadmin** → Creates **Vendor** accounts
2. **Vendors** → Login with created credentials
3. **Public** → Register → Creates **Customer** accounts

### **Authentication Flow:**
1. **Customer Registration**: Default role = 'customer'
2. **Vendor Login**: Created by superadmin
3. **Superadmin Login**: Production credentials from .env

## 📋 **TASK PLAN:**

### **Phase 1: Fix 419 CSRF Errors**
- [ ] 1.1 Check for hidden CSRF configurations
- [ ] 1.2 Remove any remaining CSRF dependencies
- [ ] 1.3 Test login functionality

### **Phase 2: Modify User Registration**
- [ ] 2.1 Force default role to 'customer' in registration
- [ ] 2.2 Restrict role selection in public signup
- [ ] 2.3 Update registration validation

### **Phase 3: Superadmin Vendor Creation**
- [ ] 3.1 Create vendor creation interface in superadmin
- [ ] 3.2 Add vendor management functionality
- [ ] 3.3 Test vendor account creation

### **Phase 4: Test Complete Flow**
- [ ] 4.1 Test customer registration
- [ ] 4.2 Test vendor login
- [ ] 4.3 Test superadmin vendor creation
- [ ] 4.4 Verify role-based access

## 🎯 **EXPECTED RESULT:**

### **Clean Authentication System:**
- ✅ **No 419 Errors**: All login/signup working
- ✅ **Customer Registration**: Public users become customers
- ✅ **Vendor Management**: Superadmin creates vendors
- ✅ **Role-based Access**: Proper permissions

### **User Creation Flow:**
```
Public Registration → Customer Account
Superadmin → Create Vendor Account
Vendor Login → Vendor Dashboard Access
```

## 🔧 **KEY CHANGES NEEDED:**

### **1. Registration Controller**
- Default role = 'customer'
- Remove role selection for public users
- Add validation

### **2. Superadmin Interface**
- Vendor creation form
- Vendor management features
- Test account creation

### **3. CSRF Cleanup**
- Find and remove remaining CSRF code
- Ensure session-only authentication

## ✅ **SUCCESS CRITERIA:**
- ✅ **Login working**: No 419 errors
- ✅ **Customer signup**: Works with customer role
- ✅ **Vendor management**: Superadmin can create vendors
- ✅ **Proper access**: Role-based dashboard access
