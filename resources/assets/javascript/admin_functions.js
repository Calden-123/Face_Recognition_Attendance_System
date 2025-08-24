//add capture student image
function openCamera(buttonId) {
  navigator.mediaDevices
    .getUserMedia({ video: true })
    .then((stream) => {
      const video = document.createElement("video");
      video.srcObject = stream;
      document.body.appendChild(video);

      video.play();

      setTimeout(() => {
        const capturedImage = captureImage(video);
        stream.getTracks().forEach((track) => track.stop());
        document.body.removeChild(video);

        const imgElement = document.getElementById(
          buttonId + "-captured-image"
        );
        imgElement.src = capturedImage;
        const hiddenInput = document.getElementById(
          buttonId + "-captured-image-input"
        );
        hiddenInput.value = capturedImage;
      }, 500);
    })
    .catch((error) => {
      console.error("Error accessing webcam:", error);
    });
}

const takeMultipleImages = async () => {
  document.getElementById("open_camera").style.display = "none";

  const images = document.getElementById("multiple-images");

  for (let i = 1; i <= 5; i++) {
    // Create the image box element
    const imageBox = document.createElement("div");
    imageBox.classList.add("image-box");

    const imgElement = document.createElement("img");
    imgElement.id = `image_${i}-captured-image`;

    const editIcon = document.createElement("div");
    editIcon.classList.add("edit-icon");

    const icon = document.createElement("i");
    icon.classList.add("fas", "fa-camera");
    icon.setAttribute("onclick", `openCamera("image_"+${i})`);

    const hiddenInput = document.createElement("input");
    hiddenInput.type = "hidden";
    hiddenInput.id = `image_${i}-captured-image-input`;
    hiddenInput.name = `capturedImage${i}`;

    editIcon.appendChild(icon);
    imageBox.appendChild(imgElement);
    imageBox.appendChild(editIcon);
    imageBox.appendChild(hiddenInput);
    images.appendChild(imageBox);
    await captureImageWithDelay(i);
  }
};

const captureImageWithDelay = async (i) => {
  try {
    // Get camera stream
    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    const video = document.createElement("video");
    video.srcObject = stream;
    document.body.appendChild(video);
    video.play();

    // Wait for 500ms before capturing the image
    await new Promise((resolve) => setTimeout(resolve, 500));

    // Capture the image
    const capturedImage = captureImage(video);

    // Stop the video stream and remove the video element
    stream.getTracks().forEach((track) => track.stop());
    document.body.removeChild(video);

    // Update the image and hidden input
    const imgElement = document.getElementById(`image_${i}-captured-image`);
    imgElement.src = capturedImage;

    const hiddenInput = document.getElementById(
      `image_${i}-captured-image-input`
    );
    hiddenInput.value = capturedImage;
  } catch (err) {
    console.error("Error accessing camera: ", err);
  }
};

function captureImage(video) {
  const canvas = document.createElement("canvas");
  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  const context = canvas.getContext("2d");

  context.drawImage(video, 0, 0, canvas.width, canvas.height);

  return canvas.toDataURL("image/png");
}

//hide and display form

// EDIT FUNCTIONALITY - NEW FUNCTIONS ADDED BELOW

// Function to open the edit modal and fetch data
function openEditModal(entity, id, name) {
    console.log('DEBUG: Opening edit modal for:', entity, 'ID:', id, 'Name:', name);
    
    // Show the form/modal that already exists for adding
    document.getElementById('form').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';

    // Change the form title to "Edit"
    const formTitle = document.querySelector('.form-title p');
    if (formTitle) {
        formTitle.textContent = `Edit ${entity.charAt(0).toUpperCase() + entity.slice(1)}`;
    }

    // Find the submit button and change its name and value for "Update"
    const submitBtn = document.querySelector('.btn-submit, .submit');
    if (submitBtn) {
        submitBtn.value = `Update ${entity.charAt(0).toUpperCase() + entity.slice(1)}`;
        submitBtn.name = `update${entity.charAt(0).toUpperCase() + entity.slice(1)}`;
        
        // Remove any existing event listeners to prevent conflicts
        const newSubmitBtn = submitBtn.cloneNode(true);
        submitBtn.parentNode.replaceChild(newSubmitBtn, submitBtn);
        
        // Add click event listener for update
        newSubmitBtn.addEventListener('click', handleFormSubmit);
    }

    // Now fetch the existing data
    const fetchUrl = '/FaceRecognitionAttendanceSystem/resources/pages/administrator/handle_fetch.php';
    console.log('DEBUG: Fetching from:', fetchUrl);
    console.log('DEBUG: Sending data:', { id: id, name: name });

    fetch(fetchUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id, name: name })
    })
    .then(response => {
        console.log('DEBUG: Response status:', response.status, response.statusText);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('DEBUG: Received response data:', data);
        if (data.success) {
            console.log('DEBUG: Success! Form data to pre-fill:', data.data);
            // Pre-fill the form with the fetched data
            const formData = data.data;
            for (const key in formData) {
                if (formData.hasOwnProperty(key) && key !== 'Id' && key !== 'dateRegistered' && key !== 'dateCreated' && key !== 'studentImage' && key !== 'password') {
                    const input = document.querySelector(`[name="${key}"]`);
                    if (input) {
                        console.log('DEBUG: Setting input', key, 'to:', formData[key]);
                        input.value = formData[key];
                    } else {
                        console.log('DEBUG: Input not found for key:', key);
                    }
                }
            }

            // Store the ID of the record being edited in a hidden field for the update
            let idInput = document.querySelector('input[name="editId"]');
            if (!idInput) {
                idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'editId';
                document.querySelector('form').appendChild(idInput);
            }
            idInput.value = id;

            // Store the entity type (student/lecture) for the update
            let entityInput = document.querySelector('input[name="editEntity"]');
            if (!entityInput) {
                entityInput = document.createElement('input');
                entityInput.type = 'hidden';
                entityInput.name = 'editEntity';
                document.querySelector('form').appendChild(entityInput);
            }
            entityInput.value = entity;

        } else {
            console.error('DEBUG: Error in response:', data.error);
            alert('Failed to fetch data for editing: ' + data.error);
        }
    })
    .catch(error => {
        console.error('DEBUG: Fetch error:', error);
        alert('Error fetching data: ' + error.message);
    });
}

// Function to handle form submission (for both add and update)
// Function to handle form submission (for both add and update)
function handleFormSubmit(e) {
    const editId = document.querySelector('input[name="editId"]');
    const editEntity = document.querySelector('input[name="editEntity"]');
    
    if (editId && editEntity) {
        // This is an edit operation - prevent default and use AJAX
        e.preventDefault();
        updateRecord(e, editId.value, editEntity.value);
    } else {
        // This is an add operation - let the form submit normally (don't prevent default)
        // The form will submit to PHP as it normally would
        return true;
    }
}

// Function to handle the update record operation
function updateRecord(e, editId, editEntity) {
    e.preventDefault();

    const form = e.target.closest('form');
    const formData = new FormData(form);

    // Build an object of data to send
    let dataToSend = {};
    for (let [key, value] of formData.entries()) {
        if (key !== 'editId' && key !== 'editEntity' && key !== 'capturedImage1' && 
            key !== 'capturedImage2' && key !== 'capturedImage3' && 
            key !== 'capturedImage4' && key !== 'capturedImage5') {
            dataToSend[key] = value;
        }
    }

    // For lecture update, exclude password if empty
    if (editEntity === 'lecture' && (!dataToSend.password || dataToSend.password === '')) {
        delete dataToSend.password;
    }

let tableName;
if (editEntity === 'student') {
    tableName = 'students'; // becomes tblstudents
} else if (editEntity === 'lecture') {
    tableName = 'lecture'; // becomes tbllecture
}

fetch('/FaceRecognitionAttendanceSystem/resources/pages/administrator/handle_update.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        id: editId,
        name: tableName, // Use the correct table name
        data: dataToSend
        })
    })
    .then(response => response.json())
.then(data => {
    if (data.success) {
        alert('Update successful!');
        closeForm();
        
        // Update the table row dynamically without refreshing
        updateTableRow(editId, editEntity, dataToSend);
    } else {
        alert('Error updating: ' + data.error);
    }
})
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during update.');
    });
}

// Function to update table row after successful edit
function updateTableRow(id, entity, updatedData) {
    const row = document.getElementById(`row${entity}${id}`);
    
    if (row) {
        // Update each cell with new data
        if (entity === 'lecture') {
            row.cells[0].textContent = updatedData.firstName || '';
            row.cells[1].textContent = updatedData.email || '';
            row.cells[2].textContent = updatedData.phoneNumber || '';
            row.cells[3].textContent = updatedData.faculty || ''; // This shows facultyCode as faculty
            // dateCreated remains the same
        } else if (entity === 'student') {
            row.cells[0].textContent = updatedData.registrationNumber || '';
            row.cells[1].textContent = updatedData.firstName || '';
            row.cells[2].textContent = updatedData.faculty || '';
            row.cells[3].textContent = updatedData.course || ''; // This shows courseCode as course
            row.cells[4].textContent = updatedData.email || '';
        }
    } else {
        // If row not found, refresh the page
        location.reload();
    }
}

// Function to close form and reset edit state
function closeForm() {
    document.getElementById('form').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
    
    // Remove edit hidden fields if they exist
    const editIdInput = document.querySelector('input[name="editId"]');
    const editEntityInput = document.querySelector('input[name="editEntity"]');
    
    if (editIdInput) editIdInput.remove();
    if (editEntityInput) editEntityInput.remove();
    
    // Reset form title and submit button if needed
    const formTitle = document.querySelector('.form-title p');
    const submitBtn = document.querySelector('.btn-submit, .submit');
    
    if (formTitle && formTitle.textContent.includes('Edit')) {
        formTitle.textContent = formTitle.textContent.replace('Edit', 'Add');
    }
    
    if (submitBtn && submitBtn.name.includes('update')) {
        submitBtn.value = submitBtn.value.replace('Update', 'Save');
        submitBtn.name = submitBtn.name.replace('update', 'add');
    }
}

// Add event listeners for the edit icons when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Event delegation for dynamically added edit icons
    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit')) {
            const id = e.target.getAttribute('data-id');
            const name = e.target.getAttribute('data-name');
            const entity = e.target.getAttribute('data-entity');
            openEditModal(entity, id, name);
        }
        
        // Close form when close button is clicked
        if (e.target.classList.contains('close')) {
            closeForm();
        }
    });

    // Add event listener to form submit button
    const submitBtn = document.querySelector('.btn-submit, .submit');
    if (submitBtn) {
        submitBtn.addEventListener('click', handleFormSubmit);
    }
});

// Function to show form for adding (complementary to closeForm)
function showAddForm() {
    closeForm(); // First close any edit state
    document.getElementById('form').style.display = 'block';
    document.getElementById('overlay').style.display = 'block';
}