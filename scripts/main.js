"use script";

function confirmDeletion(event, url) {
    // Display a confirmation dialog
    const userConfirmed = confirm("Are you sure you want to delete this Client?");
    // If the user did not confirm, prevent the navigation
    if (!userConfirmed) {
        event.preventDefault();
    } else {
        // If the user confirmed, redirect to the delete URL
        window.location.href = url;
    }
}

function confirmSubmission() {
    // Check if the checkbox is checked
    var sendEmailChecked = document.getElementById('sendEmail').checked;

    if (sendEmailChecked) {
        // If checked, show the confirmation dialog
        return confirm("Are you sure you want to send an automatic email?");
    }

    // If not checked, just submit the form without confirmation
    return true;
}




