<section class="header">
    <div class="logo">
        <h2>Attendify<span></span></h2>
    </div>
    
    <!-- Navigation items moved from sidebar -->
    <div class="navigation--items">
        <ul>
            <li>
                <a href="home">
                    <span class="icon icon-1"><i class="ri-file-text-line"></i></span>
                    <span class="navigation--item">Take Attendance</span>
                </a>
            </li>
            <li>
                <a href="view-attendance">
                    <span class="icon icon-1"><i class="ri-map-pin-line"></i></span>
                    <span class="navigation--item" style="white-space: nowrap;">View Attendance</span>
                </a>
            </li>
            <li>
                <a href="view-students">
                    <span class="icon icon-1"><i class="ri-user-line"></i></span>
                    <span class="navigation--item">Students</span>
                </a>
            </li>
        </ul>
    </div>
    
    <div class="search--notification--profile">
        <div id="searchInput" class="search">
            <input type="text" id="searchText" placeholder="Search .....">
            <button onclick="searchItems()"><i class="ri-search-2-line"></i></button>
        </div>
        <div class="notification--profile">
            <div class="picon lock">
                @ <?php echo user()->name ?>
            </div>
            <div class="picon profile">
                <img src="resources/images/user.png" alt="">
            </div>
        </div>
        
        <!-- Settings and Logout items moved from sidebar bottom -->
        <div class="logout--item">
            <a href="logout">
                <span class="icon icon-2"><i class="ri-logout-box-r-line"></i></span>
                <span class="navigation--item">Logout</span>
            </a>
        </div>
    </div>
</section>

<script>
    function searchItems() {
        var input = document.getElementById('searchText').value.toLowerCase();
        var rows = document.querySelectorAll('table tr');

        rows.forEach(function(row) {
            var cells = row.querySelectorAll('td');
            var found = false;

            cells.forEach(function(cell) {
                if (cell.innerText.toLowerCase().includes(input)) {
                    found = true;
                }
            });

            if (found) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Active link script adjusted for new navigation
    document.addEventListener("DOMContentLoaded", function() {
        var currentUrl = window.location.href;
        var links = document.querySelectorAll('.navigation--items a, .settings--item a, .logout--item a');
        links.forEach(function(link) {
            if (link.href === currentUrl) {
                link.id = 'active--link';
            }
        });
    });
</script>