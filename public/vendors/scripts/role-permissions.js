function switchRole(roleName, event) {
    event.preventDefault();

    // Update active tab
    document.querySelectorAll('.role-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    event.target.classList.add('active');

    // Update hidden input
    document.getElementById('selected-role').value = roleName;

    // Show corresponding permissions
    document.querySelectorAll('.permissions-container').forEach(container => {
        container.classList.remove('active');
    });

    const slug = roleName.toLowerCase().replace(/\s+/g, '-');
    const targetContainer = document.getElementById('permissions-' + slug);
    if (targetContainer) {
        targetContainer.classList.add('active');
    }
}

function togglePermissionDropdown(button) {
    const permissionList = button.nextElementSibling;
    const isVisible = permissionList.style.display !== 'none';

    if (isVisible) {
        permissionList.style.display = 'none';
        button.classList.remove('active');
    } else {
        permissionList.style.display = 'block';
        button.classList.add('active');
    }
}

// Update the existing form submit handler
document.querySelector('form').addEventListener('submit', function(e) {
    const selectedRole = document.getElementById('selected-role').value;
    const slug = selectedRole.toLowerCase().replace(/\s+/g, '-');

    // Disable all checkboxes that don't belong to the selected role
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        const parentContainer = checkbox.closest('.permissions-container');
        if (parentContainer && parentContainer.id !== 'permissions-' + slug) {
            checkbox.disabled = true; // Disabled inputs are not submitted
        }
    });
});
