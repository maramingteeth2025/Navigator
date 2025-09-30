// SIDEBAR DROPDOWN
const allDropdown = document.querySelectorAll('#sidebar .side-dropdown');
const sidebar = document.getElementById('sidebar');

allDropdown.forEach(item => {
	const a = item.parentElement.querySelector('a:first-child');
	a.addEventListener('click', function (e) {
		e.preventDefault();

		if (!this.classList.contains('active')) {
			allDropdown.forEach(i => {
				const aLink = i.parentElement.querySelector('a:first-child');
				aLink.classList.remove('active');
				i.classList.remove('show');
			});
		}

		this.classList.toggle('active');
		item.classList.toggle('show');
	});
});

// Select sidebar, hamburger button, and main element
const toggleSidebar = document.querySelector('nav .toggle-sidebar');
const main = document.querySelector('main');
const allSideDivider = document.querySelectorAll('#sidebar .divider');

// SIDEBAR COLLAPSE - on toggle button click
toggleSidebar.addEventListener('click', function () {
    sidebar.classList.toggle('hide'); // Toggle the 'hide' class for sidebar visibility

    // Update sidebar content accordingly
    if (sidebar.classList.contains('hide')) {
        allSideDivider.forEach(item => {
            item.textContent = '-'; // Collapse dropdowns
        });

        allDropdown.forEach(item => {
            const a = item.parentElement.querySelector('a:first-child');
            a.classList.remove('active');
            item.classList.remove('show');
        });
    } else {
        allSideDivider.forEach(item => {
            item.textContent = item.dataset.text; // Expand dropdowns
        });
    }
});

// SIDEBAR COLLAPSE - on clicking anywhere in the main content
main.addEventListener('click', function () {
    sidebar.classList.add('hide'); // Collapse the sidebar when clicking on main content
    allSideDivider.forEach(item => {
        item.textContent = '-'; // Collapse dropdowns
    });

    allDropdown.forEach(item => {
        const a = item.parentElement.querySelector('a:first-child');
        a.classList.remove('active');
        item.classList.remove('show');
    });
});


// close the navbar if click outside



// Removed mouseenter and mouseleave event listeners as they are not needed for click-based interactions

// PROFILE DROPDOWN
const profile = document.querySelector('nav .profile');
const imgProfile = profile.querySelector('img');
const dropdownProfile = profile.querySelector('.profile-link');

imgProfile.addEventListener('click', function () {
	dropdownProfile.classList.toggle('show');
});

// MENU
const allMenu = document.querySelectorAll('main .content-data .head .menu');

allMenu.forEach(item => {
	const icon = item.querySelector('.icon');
	const menuLink = item.querySelector('.menu-link');

	icon.addEventListener('click', function () {
		menuLink.classList.toggle('show');
	});
});

window.addEventListener('click', function (e) {
	if (e.target !== imgProfile && e.target !== dropdownProfile) {
		if (dropdownProfile.classList.contains('show')) {
			dropdownProfile.classList.remove('show');
		}
	}

	allMenu.forEach(item => {
		const icon = item.querySelector('.icon');
		const menuLink = item.querySelector('.menu-link');

		if (e.target !== icon && e.target !== menuLink) {
			if (menuLink.classList.contains('show')) {
				menuLink.classList.remove('show');
			}
		}
	});
});
