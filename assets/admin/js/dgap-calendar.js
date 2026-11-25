document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('dgap-calendar');
    if (!calendarEl) {
        return;
    }

    // Create reusable floating popover for full booking details
    const popover = document.createElement('div');
    popover.id = 'dgap-fc-popover';
    popover.className = 'dgap-fc-popover';
    popover.style.display = 'none';
    document.body.appendChild(popover);

    let pinnedByClick = false;
    let hideTimeout = null;

    function formatTime(dateObj) {
        if (!dateObj) return '';
        return dateObj.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    }

    function formatDate(dateObj) {
        if (!dateObj) return '';
        return dateObj.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
    }

    function parseTitle(title) {
        const parts = (title || '').split(' – ');
        return {
            customer: parts[0] || title || 'Booking',
            service: parts.length > 1 ? parts.slice(1).join(' – ') : ''
        };
    }

    function hidePopover() {
        pinnedByClick = false;
        popover.style.display = 'none';
    }

    function showPopover(info) {
        clearTimeout(hideTimeout);

        const ev = info.event;
        const props = ev.extendedProps || {};
        const parsed = parseTitle(ev.title);
        const status = (props.status || 'confirmed').toLowerCase();
        const statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
        const statusClass = status === 'pending' ? 'is-warning' : (status === 'cancelled' ? 'is-danger' : 'is-success');

        const timeRange = ev.end
            ? formatTime(ev.start) + ' – ' + formatTime(ev.end)
            : formatTime(ev.start);
        const priceVal = props.price !== undefined && props.price !== null
            ? '$' + parseFloat(props.price).toFixed(2)
            : '';

        // Build popover safely with DOM nodes
        popover.innerHTML = '';

        const header = document.createElement('div');
        header.className = 'dgap-fc-pop-header';

        const titleEl = document.createElement('strong');
        titleEl.textContent = parsed.customer;

        const badge = document.createElement('span');
        badge.className = 'dgap-status-pill ' + statusClass;
        badge.textContent = statusLabel;

        header.appendChild(titleEl);
        header.appendChild(badge);
        popover.appendChild(header);

        const list = document.createElement('div');
        list.className = 'dgap-fc-pop-meta';

        const rows = [
            { icon: 'dashicons-admin-tools', text: parsed.service },
            { icon: 'dashicons-calendar-alt', text: formatDate(ev.start) },
            { icon: 'dashicons-clock', text: timeRange },
            { icon: 'dashicons-admin-users', text: props.staff ? 'Staff: ' + props.staff : '' },
            { icon: 'dashicons-tag', text: priceVal ? 'Price: ' + priceVal : '' }
        ];

        rows.forEach(function(r) {
            if (!r.text) return;
            const row = document.createElement('div');
            row.className = 'dgap-fc-pop-row';
            const ic = document.createElement('span');
            ic.className = 'dashicons ' + r.icon;
            const txt = document.createElement('span');
            txt.textContent = r.text;
            row.appendChild(ic);
            row.appendChild(txt);
            list.appendChild(row);
        });

        popover.appendChild(list);
        popover.style.display = 'block';

        // Position near event element, keeping within viewport
        const rect = info.el.getBoundingClientRect();
        const popRect = popover.getBoundingClientRect();
        const margin = 8;

        let top = rect.bottom + window.scrollY + margin;
        let left = rect.left + window.scrollX;

        if (rect.bottom + popRect.height + margin > window.innerHeight) {
            top = rect.top + window.scrollY - popRect.height - margin;
        }
        if (left + popRect.width > window.scrollX + window.innerWidth - 16) {
            left = window.scrollX + window.innerWidth - popRect.width - 16;
        }
        if (left < window.scrollX + 12) {
            left = window.scrollX + 12;
        }

        popover.style.top = top + 'px';
        popover.style.left = left + 'px';
    }

    popover.addEventListener('mouseenter', function() {
        clearTimeout(hideTimeout);
    });
    popover.addEventListener('mouseleave', function() {
        if (!pinnedByClick) {
            hideTimeout = setTimeout(hidePopover, 150);
        }
    });

    document.addEventListener('click', function(e) {
        if (!popover.contains(e.target) && !e.target.closest('.fc-event')) {
            hidePopover();
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hidePopover();
        }
    });

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        editable: false,
        dayMaxEvents: 3,
        eventDisplay: 'block',
        eventClassNames: function(arg) {
            const status = (arg.event.extendedProps && arg.event.extendedProps.status)
                ? arg.event.extendedProps.status.toLowerCase()
                : 'confirmed';
            if (status === 'pending') return ['dgap-fc-pending'];
            if (status === 'cancelled') return ['dgap-fc-cancelled'];
            return ['dgap-fc-confirmed'];
        },
        eventContent: function(arg) {
            const parsed = parseTitle(arg.event.title);
            const timeStr = formatTime(arg.event.start);

            const wrap = document.createElement('div');
            wrap.className = 'dgap-fc-ev-wrap';

            const top = document.createElement('div');
            top.className = 'dgap-fc-ev-top';

            if (timeStr) {
                const timeEl = document.createElement('span');
                timeEl.className = 'dgap-fc-ev-time';
                timeEl.textContent = timeStr;
                top.appendChild(timeEl);
            }

            const nameEl = document.createElement('span');
            nameEl.className = 'dgap-fc-ev-name';
            nameEl.textContent = parsed.customer;
            top.appendChild(nameEl);

            wrap.appendChild(top);

            if (parsed.service) {
                const srvEl = document.createElement('div');
                srvEl.className = 'dgap-fc-ev-service';
                srvEl.textContent = parsed.service;
                wrap.appendChild(srvEl);
            }

            return { domNodes: [wrap] };
        },
        eventMouseEnter: function(info) {
            if (!pinnedByClick) {
                showPopover(info);
            }
        },
        eventMouseLeave: function() {
            if (!pinnedByClick) {
                hideTimeout = setTimeout(hidePopover, 180);
            }
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            pinnedByClick = true;
            showPopover(info);
        },
        eventSources: [
            {
                url: dgap_calendar.ajax_url,
                method: 'POST',
                extraParams: {
                    action: 'dgap_get_calendar_bookings',
                    _dgap_nonce : dgap_calendar.nonce
                },
                failure: function() {
                    alert('There was an error fetching bookings!');
                }
            }
        ],
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        }
    });

    calendar.render();
});
