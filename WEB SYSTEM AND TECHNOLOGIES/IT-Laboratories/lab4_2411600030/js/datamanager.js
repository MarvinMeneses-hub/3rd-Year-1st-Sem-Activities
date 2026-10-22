// ================================
// DATA MANAGER
// ================================

const DataManager = {

    // ================================
    // PRODUCT DATA
    // ================================

    products: [

        {
            id: 1,
            product_name: "Laptop",
            sku: "LAP-001",
            category: "Electronics",
            quantity: 15,
            unit_price: 35000,
            reorder_level: 5,
            supplier: "Tech Supplier"
        },

        {
            id: 2,
            product_name: "Keyboard",
            sku: "KEY-001",
            category: "Accessories",
            quantity: 25,
            unit_price: 1200,
            reorder_level: 10,
            supplier: "Computer Store"
        },

        {
            id: 3,
            product_name: "Mouse",
            sku: "MOU-001",
            category: "Accessories",
            quantity: 8,
            unit_price: 750,
            reorder_level: 10,
            supplier: "Computer Store"
        },

        {
            id: 4,
            product_name: "Monitor",
            sku: "MON-001",
            category: "Electronics",
            quantity: 12,
            unit_price: 8500,
            reorder_level: 5,
            supplier: "Display Supplier"
        },

        {
            id: 5,
            product_name: "Printer",
            sku: "PRI-001",
            category: "Office Equipment",
            quantity: 6,
            unit_price: 12000,
            reorder_level: 5,
            supplier: "Office Supplier"
        },

        {
            id: 6,
            product_name: "USB Flash Drive",
            sku: "USB-001",
            category: "Storage",
            quantity: 30,
            unit_price: 500,
            reorder_level: 10,
            supplier: "Storage Supplier"
        },

        {
            id: 7,
            product_name: "External Hard Drive",
            sku: "HDD-001",
            category: "Storage",
            quantity: 4,
            unit_price: 4500,
            reorder_level: 5,
            supplier: "Storage Supplier"
        },

        {
            id: 8,
            product_name: "Webcam",
            sku: "WEB-001",
            category: "Accessories",
            quantity: 10,
            unit_price: 2500,
            reorder_level: 5,
            supplier: "Computer Store"
        },

        {
            id: 9,
            product_name: "Router",
            sku: "ROU-001",
            category: "Networking",
            quantity: 7,
            unit_price: 3500,
            reorder_level: 5,
            supplier: "Network Supplier"
        },

        {
            id: 10,
            product_name: "Network Cable",
            sku: "CAB-001",
            category: "Networking",
            quantity: 40,
            unit_price: 150,
            reorder_level: 15,
            supplier: "Network Supplier"
        }

    ],


    // ================================
    // SIMULATED ASYNC DATA RETRIEVAL
    // ================================

    // Simulate an API request with a short delay. A real application could
    // replace this method with fetch("/api/products") and parse JSON.
    fetchProducts: async function () {
        await new Promise(function (resolve) {
            window.setTimeout(resolve, 800);
        });

        return this.products;
    },


    // ================================
    // GET ALL PRODUCTS
    // ================================

    getProducts: function () {

        return this.products;
    },


    // ================================
    // GET LOW STOCK PRODUCTS
    // ================================

    getLowStockProducts: function () {

        return this.products.filter(function (product) {

            return product.quantity <= product.reorder_level;

        });
    },


    // ================================
    // GET TOTAL INVENTORY VALUE
    // ================================

    getInventoryValue: function () {

        return this.products.reduce(
            function (total, product) {

                return total +
                    (product.quantity * product.unit_price);

            },
            0
        );
    },


    // ================================
    // GET CATEGORIES
    // ================================

    getCategories: function () {

        return [
            ...new Set(
                this.products.map(function (product) {
                    return product.category;
                })
            )
        ];
    }

};