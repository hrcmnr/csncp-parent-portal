<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pre-Registration</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap-icons.css">
    <link rel="stylesheet" href="styles_pre_registration.css"> <!-- Link to custom CSS for additional styling -->
</head>

<body>
    <div class="container">
        <h2 class="text-center">Pre-Registration</h2>
        <form action="submit_pre_registration.php" method="POST" enctype="multipart/form-data" class="mt-3" onsubmit="return validateForm()">
            <!-- Parent's Information -->
            <div class="form-group">
                <label for="surname">Surname:</label>
                <input type="text" id="surname" name="surname" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="middle_name">Middle Name (optional):</label>
                <input type="text" id="middle_name" name="middle_name" class="form-control">
            </div>
            <div class="form-group">
                <label for="role">Are you a Mother/Father/Guardian:</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="">Select...</option>
                    <option value="Mother">Mother</option>
                    <option value="Father">Father</option>
                    <option value="Guardian">Guardian</option>
                </select>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="parent_picture">Upload Parent's Picture:</label>
                <input type="file" id="parent_picture" name="parent_picture" class="form-control-file" accept="image/*" required>
            </div>

            <!-- Child's Information -->
            <h4 class="mt-4">Child's Information</h4>
            <div class="form-group">
                <label for="child_surname">Child's Surname:</label>
                <input type="text" id="child_surname" name="child_surname" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="child_first_name">Child's First Name:</label>
                <input type="text" id="child_first_name" name="child_first_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="child_middle_name">Child's Middle Name (optional):</label>
                <input type="text" id="child_middle_name" name="child_middle_name" class="form-control">
            </div>
            <div class="form-group">
                <label for="child_diagnosis">Child's Diagnosis:</label>
                <input type="text" id="child_diagnosis" name="child_diagnosis" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="child_picture">Upload Child's Picture:</label>
                <input type="file" id="child_picture" name="child_picture" class="form-control-file" accept="image/*" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Submit</button>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies (Optional) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function validateForm() {
            const requiredFields = [
                document.getElementById('surname'),
                document.getElementById('first_name'),
                document.getElementById('role'),
                document.getElementById('email'),
                document.getElementById('child_surname'),
                document.getElementById('child_first_name'),
                document.getElementById('child_diagnosis')
            ];

            for (let field of requiredFields) {
                if (!field.value.trim()) {
                    alert(`${field.previousElementSibling.innerText} is required.`);
                    field.focus();
                    return false; // Prevent form submission
                }
            }
            return true; // Allow form submission
        }
    </script>
</body>

</html>
