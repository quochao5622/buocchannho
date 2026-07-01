document.addEventListener("scroll-to-top", () => window.scrollTo(0, 0));

(function () {
    const sidebarCollapseVersion = "2";

    if (localStorage.getItem("sidebarCollapseVersion") !== sidebarCollapseVersion) {
        localStorage.removeItem("collapsedGroups");
        localStorage.setItem("sidebarCollapseVersion", sidebarCollapseVersion);
    }

    const expandActiveNavigationGroups = () => {
        if (! window.Alpine) {
            return;
        }

        const store = Alpine.store("sidebar");

        if (! store) {
            return;
        }

        document
            .querySelectorAll(".fi-main-sidebar .fi-sidebar-group.fi-active")
            .forEach((group) => {
                const label = group.dataset.groupLabel;

                if (! label || ! store.groupIsCollapsed(label)) {
                    return;
                }

                store.toggleCollapsedGroup(label);
            });
    };

    document.addEventListener("alpine:initialized", expandActiveNavigationGroups);
    document.addEventListener("livewire:navigated", expandActiveNavigationGroups);
})();
