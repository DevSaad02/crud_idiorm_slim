$(document).ready(function () {
    // On page load get users from the database
    getUsers();
    // Fetch users from the database
    function getUsers() {
        $.ajax({
            url: "users/list", // Endpoint
            type: "GET",
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    $("tbody").empty(); // Clear existing rows before appending new ones
                    response.data.forEach(function (user) {
                        $("tbody").append(`
                            <tr>
                                <td>${user.name}</td>
                                <td>${user.email}</td>
                                <td>${user.phone}</td>
                                <td>
                                    <button class="btn btn-primary btn-sm btn-edit" id="userId" name="userId" value="${user.id}">Edit</button>
                                    <button class="btn btn-danger btn-sm btn-delete" id="userId" name="userId" value="${user.id}">Delete</button>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error: " + error);
                alert("Something went wrong!");
            }
        });
    }

    // Add user form submission request
    $("#addUserForm").submit(function (event) {
        event.preventDefault(); // Prevent default form submission

        $.ajax({
            url: "users/store", // Endpoint
            type: "POST",
            data: $(this).serialize(), // Serialize form data
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    alert("User added successfully!");
                    $("#addUserForm")[0].reset(); // Reset form after submission
                    $("#addUserForm").closest(".modal").modal("hide"); // Hide modal
                    getUsers(); // Reload users
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error: " + error);
                alert("Something went wrong!");
            }
        });
    });

    // Edit user request
    $(document).on("click", ".btn-edit", function () {
        let userId = $(this).val(); // Get the user ID from button value
        $.ajax({
            url: "/users/edit", // Backend script to fetch user
            type: "POST",
            data: { userId: userId },
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    // Populate modal body with fetched user data
                    $("#editUserModal .modal-body").html(`
                            <div class="mb-3">
                                <label for="editName" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="${response.data.name}" required>
                            </div>
                            <div class="mb-3">
                                <label for="editEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="${response.data.email}" required>
                            </div>
                            <div class="mb-3">
                                <label for="editPhone" class="form-label">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="${response.data.phone}" required>
                            </div>
                            <input type="hidden" id="userId" name="userId" value="${response.data.id}">
                        `);

                    // Show the modal
                    $("#editUserModal").modal("show");
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error: " + error);
                alert("Something went wrong!");
            }
        });
    });

    //Update user form submission request
    $("#editUserForm").submit(function (event) {
        event.preventDefault(); // Prevent default form submission

        $.ajax({
            url: "/users/update", // Endpoint
            type: "PUT",
            data: $(this).serialize(), // Serialize form data
            dataType: "json",
            success: function (response) {
                if (response.status === 'success') {
                    alert("User updated successfully!");
                    $("#editUserForm")[0].reset(); // Reset form after submission
                    $("#editUserForm").closest(".modal").modal("hide"); // Hide modal
                    // Reload users
                    getUsers();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error: " + error);
                alert("Something went wrong!");
            }
        });
    });

    // Delete user request
    $(document).on("click", ".btn-delete", function () {
        let userId = $(this).val(); // Get the user ID from button value
        if (confirm("Are you sure you want to delete this user?")) {
            $.ajax({
                url: "/users/delete", // Endpoint
                type: "DELETE",
                data: {
                    userId: userId
                },
                dataType: "json",
                success: function (response) {
                    if (response.status === "success") {
                        alert("User deleted successfully!");
                        // Reload users
                        getUsers();
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error: " + error);
                    alert("Something went wrong!");
                }
            });
        }
    });
});