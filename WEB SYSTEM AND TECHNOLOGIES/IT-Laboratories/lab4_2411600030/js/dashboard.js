// ================================
// DASHBOARD SCRIPT
// ================================

document.addEventListener("DOMContentLoaded", async function () {

    // ================================
    // CHECK LOGIN
    // ================================

    const isLoggedIn = localStorage.getItem("isLoggedIn");

    if (isLoggedIn !== "true") {
        window.location.href = "index.html";
        return;
    }


    // ================================
    // USER INFORMATION
    // ================================

    const username = localStorage.getItem("username") || "Student";

    const welcomeName = document.getElementById("welcomeName");

    if (welcomeName) {
        welcomeName.textContent = username;
    }


    // ================================
    // GET INVENTORY DATA
    // ================================

    // Show a status message while simulating an asynchronous API request.
    let inventoryDataStatus = document.getElementById("inventoryDataStatus");
    if (!inventoryDataStatus) {
        inventoryDataStatus = document.createElement("div");
        inventoryDataStatus.id = "inventoryDataStatus";
        inventoryDataStatus.className = "alert alert-info mt-2";
        inventoryDataStatus.setAttribute("role", "status");
        const inventorySection = document.getElementById("inventory");
        if (inventorySection) inventorySection.insertBefore(inventoryDataStatus, inventorySection.firstChild);
    }
    inventoryDataStatus.textContent = "Loading inventory data...";

    // Simulate fetching inventory from an API asynchronously. Fall back to the
    // local sample data if the simulated request fails.
    let products = DataManager.getProducts();
    try {
        products = await DataManager.fetchProducts();
        inventoryDataStatus.textContent = "Inventory data loaded successfully.";
        inventoryDataStatus.className = "alert alert-success mt-2";
        window.setTimeout(function () {
            inventoryDataStatus.classList.add("d-none");
        }, 2500);
    } catch (error) {
        console.error("Unable to load inventory data:", error);
        products = DataManager.getProducts();
        inventoryDataStatus.textContent = "Using local sample data because data retrieval failed.";
        inventoryDataStatus.className = "alert alert-warning mt-2";
    }

    // Shared inventory data used by the table, statistics, alerts, and charts.
    // This remains the same DataManager array used by simulated stock movements.
    const chartInstances = {};
    let simulationTimer = null;
    let visibleProducts = [...products];

    function getCurrentLowStockProducts() {
        return products.filter(function (product) {
            return Number(product.quantity) > 0 &&
                Number(product.quantity) <= Number(product.reorder_level);
        });
    }

    function getCurrentInventoryValue() {
        return products.reduce(function (total, product) {
            return total + Number(product.quantity) * Number(product.unit_price);
        }, 0);
    }

    function getStockStatus(product) {
        if (Number(product.quantity) <= 0) return "out-of-stock";
        if (Number(product.quantity) <= Number(product.reorder_level)) return "low-stock";
        return "in-stock";
    }

    function addActivity(description, status) {
        const table = document.getElementById("activityTable");
        if (!table) return;
        const row = document.createElement("tr");
        row.innerHTML = `<td>Inventory Update</td><td>${description}</td><td>${status || "Just now"}</td>`;
        table.insertBefore(row, table.firstChild);
        while (table.children.length > 6) table.removeChild(table.lastChild);
    }

    function showInventoryNotification(message) {
        let notice = document.getElementById("inventoryUpdateNotice");
        if (!notice) {
            notice = document.createElement("div");
            notice.id = "inventoryUpdateNotice";
            notice.className = "alert alert-info mt-3";
            notice.setAttribute("role", "status");
            const section = document.getElementById("inventory");
            if (section) section.insertBefore(notice, section.firstChild);
        }
        notice.textContent = message;
        notice.classList.remove("d-none");
        if (notice._dismissTimer) window.clearTimeout(notice._dismissTimer);
        notice._dismissTimer = window.setTimeout(function () {
            notice.classList.add("d-none");
        }, 5000);
    }


    // ================================
    // DASHBOARD STATISTICS
    // ================================

    function renderStatistics() {
        const lowStockProducts = getCurrentLowStockProducts();
        const inventoryValue = getCurrentInventoryValue();

        const stat1Title = document.getElementById("stat1-title");
        const stat1Value = document.getElementById("stat1-value");
        const stat2Title = document.getElementById("stat2-title");
        const stat2Value = document.getElementById("stat2-value");
        const stat3Title = document.getElementById("stat3-title");
        const stat3Value = document.getElementById("stat3-value");
        const stat4Title = document.getElementById("stat4-title");
        const stat4Value = document.getElementById("stat4-value");

        if (stat1Title) stat1Title.textContent = "Course";
        if (stat1Value) stat1Value.textContent = "BSIT";
        if (stat2Title) stat2Title.textContent = "Total Products";
        if (stat2Value) stat2Value.textContent = products.length;
        if (stat3Title) stat3Title.textContent = "Low Stock";
        if (stat3Value) stat3Value.textContent = lowStockProducts.length;
        if (stat4Title) stat4Title.textContent = "Inventory Value";
        if (stat4Value) stat4Value.textContent = formatCurrency(inventoryValue);
    }


    // ================================
    // RECENT ACTIVITY
    // ================================

    const activityTable = document.getElementById("activityTable");

    if (activityTable) {

        activityTable.innerHTML = `
            <tr>
                <td>Login</td>
                <td>Student logged into the dashboard</td>
                <td>Today</td>
            </tr>

            <tr>
                <td>Inventory</td>
                <td>Inventory data loaded successfully</td>
                <td>Today</td>
            </tr>

            <tr>
                <td>System</td>
                <td>Dashboard initialized</td>
                <td>Today</td>
            </tr>
        `;
    }


    // ================================
    // INVENTORY FILTER ELEMENTS
    // ================================

    const searchInput = document.getElementById("searchInput");
    const categoryFilter = document.getElementById("categoryFilter");
    const statusFilter = document.getElementById("statusFilter");

    const minPrice = document.getElementById("minPrice");
    const maxPrice = document.getElementById("maxPrice");

    const resetButton = document.getElementById("resetButton");
    const exportButton = document.getElementById("exportButton");


    // ================================
    // POPULATE CATEGORY FILTER
    // ================================

    if (categoryFilter) {

        const categories = DataManager.getCategories();

        categories.forEach(function (category) {

            const option = document.createElement("option");

            option.value = category;
            option.textContent = category;

            categoryFilter.appendChild(option);
        });
    }


    // ================================
    // INVENTORY TABLE
    // ================================

    function renderInventory() {

        const inventoryTable = document.getElementById("inventoryTable");

        if (!inventoryTable) {
            return;
        }

        const searchValue = searchInput
            ? searchInput.value.toLowerCase().trim()
            : "";

        const selectedCategory = categoryFilter
            ? categoryFilter.value
            : "";

        const selectedStatus = statusFilter
            ? statusFilter.value
            : "";

        const minimumPrice = minPrice && minPrice.value !== ""
            ? parseFloat(minPrice.value)
            : null;

        const maximumPrice = maxPrice && maxPrice.value !== ""
            ? parseFloat(maxPrice.value)
            : null;


        const filteredProducts = products.filter(function (product) {

            // Search filter
            const matchesSearch =
                product.product_name.toLowerCase().includes(searchValue) ||
                product.sku.toLowerCase().includes(searchValue) ||
                product.category.toLowerCase().includes(searchValue) ||
                product.supplier.toLowerCase().includes(searchValue);


            // Category filter
            const matchesCategory =
                selectedCategory === "all" ||
                selectedCategory === "" ||
                product.category === selectedCategory;


            // Stock status filter
            let matchesStatus = true;

            if (selectedStatus === "low-stock") {
                matchesStatus =
                    product.quantity > 0 &&
                    product.quantity <= product.reorder_level;
            } else if (selectedStatus === "in-stock") {
                matchesStatus =
                    product.quantity > product.reorder_level;
            } else if (selectedStatus === "out-of-stock") {
                matchesStatus = product.quantity <= 0;
            }


            // Minimum price
            const matchesMinPrice =
                minimumPrice === null ||
                product.unit_price >= minimumPrice;


            // Maximum price
            const matchesMaxPrice =
                maximumPrice === null ||
                product.unit_price <= maximumPrice;


            return (
                matchesSearch &&
                matchesCategory &&
                matchesStatus &&
                matchesMinPrice &&
                matchesMaxPrice
            );

        });

        // Keep the current filtered view in shared state so charts and table
        // always represent the same set of products.
        visibleProducts = filteredProducts;

        inventoryTable.innerHTML = "";


        // ================================
        // NO RESULTS
        // ================================

        if (filteredProducts.length === 0) {

            inventoryTable.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center">
                        No products found.
                    </td>
                </tr>
            `;

        } else {

            filteredProducts.forEach(function (product) {

                const row = document.createElement("tr");

                const isLowStock =
                    product.quantity <= product.reorder_level;


                const statusBadge = isLowStock
                    ? `<span class="badge bg-danger">Low Stock</span>`
                    : `<span class="badge bg-success">In Stock</span>`;


                row.innerHTML = `
                    <td>${product.product_name}</td>
                    <td>${product.sku}</td>
                    <td>${product.category}</td>
                    <td>${product.quantity}</td>
                    <td>₱${Number(product.unit_price).toLocaleString("en-PH", {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })}</td>
                    <td>${product.reorder_level}</td>
                    <td>${product.supplier}</td>
                    <td>${product.quantity <= 0
                        ? '<span class="badge bg-secondary">Out of Stock</span>'
                        : statusBadge}</td>
                `;

                inventoryTable.appendChild(row);
            });
        }


        // ================================
        // INVENTORY COUNT
        // ================================

        const inventoryCount =
            document.getElementById("inventoryCount");

        if (inventoryCount) {

            inventoryCount.textContent =
                `Showing ${filteredProducts.length} of ${products.length} products`;
        }
    }


    // ================================
    // LOW STOCK ALERT
    // ================================

    function renderLowStockAlert() {

        const lowStockAlert =
            document.getElementById("lowStockAlert");

        if (!lowStockAlert) {
            return;
        }


        const lowStockProducts = getCurrentLowStockProducts();

        if (lowStockProducts.length === 0) {

            lowStockAlert.className =
                "alert alert-success";

            lowStockAlert.innerHTML = `
                <strong>Inventory Status:</strong>
                All products have sufficient stock.
            `;

            return;
        }


        const productNames = lowStockProducts
            .map(function (product) {
                return `${product.product_name} (${product.quantity} left)`;
            })
            .join(", ");


        lowStockAlert.className =
            "alert alert-warning";

        lowStockAlert.innerHTML = `
            <strong>Low Stock Alert:</strong>
            ${productNames}
        `;
    }


    // ================================
    // CURRENCY FORMAT
    // ================================

    function formatCurrency(value) {

        return Number(value).toLocaleString("en-PH", {
            style: "currency",
            currency: "PHP",
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }


    // ================================
    // CHARTS
    // ================================

    function renderCharts() {

        // Check if Chart.js is available before creating any charts.
        if (typeof Chart === "undefined") {
            console.warn("Chart.js is not loaded.");
            return;
        }

        // Inventory value by category (quantity multiplied by unit price).
        const categoryCanvas = document.getElementById("categoryChart");

        if (categoryCanvas) {
            const categories = [...new Set(visibleProducts.map(function (product) {
                return product.category;
            }))];
            const categoryValues = categories.map(function (category) {
                return visibleProducts
                    .filter(function (product) {
                        return product.category === category;
                    })
                    .reduce(function (total, product) {
                        return total + (Number(product.quantity) * Number(product.unit_price));
                    }, 0);
            });

            if (chartInstances.category) chartInstances.category.destroy();
            chartInstances.category = new Chart(categoryCanvas, {
                type: "bar",
                data: {
                    labels: categories,
                    datasets: [{
                        label: "Inventory Value (PHP)",
                        data: categoryValues,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return "Inventory Value: " + formatCurrency(context.raw);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return "₱" + Number(value).toLocaleString("en-PH");
                                }
                            }
                        }
                    }
                }
            });
        }

        // Stock status distribution: out of stock, low stock, and in stock.
        const stockCanvas = document.getElementById("stockChart");

        if (stockCanvas) {
            const outOfStock = visibleProducts.filter(function (product) {
                return Number(product.quantity) <= 0;
            }).length;
            const lowStock = visibleProducts.filter(function (product) {
                return Number(product.quantity) > 0 &&
                    Number(product.quantity) <= Number(product.reorder_level);
            }).length;
            const inStock = visibleProducts.filter(function (product) {
                return Number(product.quantity) > Number(product.reorder_level);
            }).length;

            if (chartInstances.stock) chartInstances.stock.destroy();
            chartInstances.stock = new Chart(stockCanvas, {
                type: "doughnut",
                data: {
                    labels: ["In Stock", "Low Stock", "Out of Stock"],
                    datasets: [{
                        data: [inStock, lowStock, outOfStock],
                        backgroundColor: ["#198754", "#ffc107", "#dc3545"],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: "bottom" }
                    }
                }
            });
        }

        // Top five products ranked by inventory value, not by quantity alone.
        const topProductsCanvas = document.getElementById("topProductsChart");

        if (topProductsCanvas) {
            const topProducts = [...visibleProducts]
                .sort(function (a, b) {
                    return (Number(b.quantity) * Number(b.unit_price)) -
                        (Number(a.quantity) * Number(a.unit_price));
                })
                .slice(0, 5);

            if (chartInstances.topProducts) chartInstances.topProducts.destroy();
            chartInstances.topProducts = new Chart(topProductsCanvas, {
                type: "bar",
                data: {
                    labels: topProducts.map(function (product) {
                        return product.product_name;
                    }),
                    datasets: [{
                        label: "Inventory Value (PHP)",
                        data: topProducts.map(function (product) {
                            return Number(product.quantity) * Number(product.unit_price);
                        }),
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: "y",
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return "Inventory Value: " + formatCurrency(context.raw);
                                }
                            }
                        },
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return "₱" + Number(value).toLocaleString("en-PH");
                                }
                            }
                        }
                    }
                }
            });
        }
    }


    // ================================
    // SYNCHRONIZED REFRESH + SIMULATED LIVE UPDATES
    // ================================

    function refreshInventoryDashboard() {
        renderInventory();
        renderStatistics();
        renderLowStockAlert();
        renderCharts();
    }

    function simulateInventoryUpdate() {
        if (!products.length) return;

        const product = products[Math.floor(Math.random() * products.length)];
        const previousQuantity = Number(product.quantity);
        const isStockIn = Math.random() >= 0.5;
        let nextQuantity = isStockIn
            ? previousQuantity + Math.floor(Math.random() * 5) + 1
            : Math.max(0, previousQuantity - (Math.floor(Math.random() * 5) + 1));

        if (nextQuantity === previousQuantity) {
            nextQuantity = previousQuantity > 0 ? previousQuantity - 1 : previousQuantity + 1;
        }

        product.quantity = nextQuantity;
        const movement = nextQuantity >= previousQuantity ? "Stock received" : "Stock issued";
        const message = `${product.product_name}: ${previousQuantity} → ${nextQuantity} units (${movement.toLowerCase()}).`;

        addActivity(`${product.product_name}: ${previousQuantity} → ${nextQuantity} units (${movement})`, "Just now");
        refreshInventoryDashboard();
        showInventoryNotification("Simulated inventory update — " + message);
    }

    // Simulate a stock movement every 30 seconds while the dashboard is open.
    simulationTimer = window.setInterval(simulateInventoryUpdate, 30000);
    window.addEventListener("beforeunload", function () {
        if (simulationTimer !== null) window.clearInterval(simulationTimer);
    });


    // ================================
    // FILTER EVENTS
    // ================================

    if (searchInput) {
        searchInput.addEventListener("input", function () {
            renderInventory();
            renderCharts();
        });
    }

    if (categoryFilter) {
        categoryFilter.addEventListener("change", function () {
            renderInventory();
            renderCharts();
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener("change", function () {
            renderInventory();
            renderCharts();
        });
    }

    if (minPrice) {
        minPrice.addEventListener("input", function () {
            renderInventory();
            renderCharts();
        });
    }

    if (maxPrice) {
        maxPrice.addEventListener("input", function () {
            renderInventory();
            renderCharts();
        });
    }


    // ================================
    // RESET FILTERS
    // ================================

    if (resetButton) {

        resetButton.addEventListener("click", function () {

            if (searchInput) {
                searchInput.value = "";
            }

            if (categoryFilter) {
                categoryFilter.value = "all";
            }

            if (statusFilter) {
                statusFilter.value = "all";
            }

            if (minPrice) {
                minPrice.value = "";
            }

            if (maxPrice) {
                maxPrice.value = "";
            }

            renderInventory();
            renderCharts();
        });
    }


    // ================================
    // EXPORT INVENTORY TO CSV
    // ================================

    if (exportButton) {

        exportButton.addEventListener("click", function () {

            const rows = [];

            rows.push([
                "ID",
                "Product Name",
                "SKU",
                "Category",
                "Quantity",
                "Unit Price",
                "Reorder Level",
                "Supplier"
            ]);


            const searchValue = searchInput ? searchInput.value.toLowerCase().trim() : "";
            const selectedCategory = categoryFilter ? categoryFilter.value : "all";
            const selectedStatus = statusFilter ? statusFilter.value : "all";
            const minimumPrice = minPrice && minPrice.value !== "" ? Number(minPrice.value) : null;
            const maximumPrice = maxPrice && maxPrice.value !== "" ? Number(maxPrice.value) : null;

            const exportProducts = products.filter(function (product) {
                const matchesSearch =
                    product.product_name.toLowerCase().includes(searchValue) ||
                    product.sku.toLowerCase().includes(searchValue) ||
                    product.category.toLowerCase().includes(searchValue) ||
                    product.supplier.toLowerCase().includes(searchValue);
                const matchesCategory = selectedCategory === "all" || selectedCategory === "" ||
                    product.category === selectedCategory;
                const matchesStatus = selectedStatus === "all" || selectedStatus === "" ||
                    getStockStatus(product) === selectedStatus;
                const matchesMin = minimumPrice === null || Number(product.unit_price) >= minimumPrice;
                const matchesMax = maximumPrice === null || Number(product.unit_price) <= maximumPrice;
                return matchesSearch && matchesCategory && matchesStatus && matchesMin && matchesMax;
            });

            exportProducts.forEach(function (product) {

                rows.push([
                    product.id,
                    product.product_name,
                    product.sku,
                    product.category,
                    product.quantity,
                    product.unit_price,
                    product.reorder_level,
                    product.supplier
                ]);
            });


            const csvContent = rows
                .map(function (row) {

                    return row
                        .map(function (value) {

                            const safeValue =
                                String(value).replace(/"/g, '""');

                            return `"${safeValue}"`;

                        })
                        .join(",");
                })
                .join("\n");


            const blob = new Blob(
                [csvContent],
                {
                    type: "text/csv;charset=utf-8;"
                }
            );


            const url =
                URL.createObjectURL(blob);


            const link =
                document.createElement("a");

            link.href = url;

            link.download =
                "inventory_export.csv";


            document.body.appendChild(link);

            link.click();

            document.body.removeChild(link);

            URL.revokeObjectURL(url);
        });
    }


    // ================================
    // LOGOUT
    // ================================

    const logoutButtons =
        document.querySelectorAll(
            "#logoutButton, #logoutLink, .logout-button"
        );


    logoutButtons.forEach(function (button) {

        button.addEventListener("click", function (event) {

            event.preventDefault();

            localStorage.removeItem("isLoggedIn");
            localStorage.removeItem("username");

            window.location.href = "index.html";
        });
    });


    // ================================
    // INITIALIZE DASHBOARD
    // ================================

    refreshInventoryDashboard();

});