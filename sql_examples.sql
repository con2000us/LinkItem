INSERT INTO `links` (`link_id`, `page_name`, `page_info`, `lanhost`, `lanport`, `lanDir`, `outerhost`, `outerport`, `outerDir`, `cellCSS`, `linkOrder`) VALUES
(1, 'Unraid主控制頁', 'i7-13700', '192.168.100.107', 80, '', 'mayacraft.net', 6806, '', '{
  "cardClass": "custom-card",
  "cardStyle": {
    "borderLeft": "5px solid #9b59b6"
  },
  "numberStyle": {
    "backgroundColor": "#f8f9fa",
    "color": "#9b59b6"
  },
  "contentStyle": {
    "backgroundColor": "#9b59b6",
    "--card-bg": "#9b59b6"
  },
  "icon": "fas fa-server"
}', 0),
(2, 'NAS存储服务', 'Synology DS920+', '192.168.100.108', 5000, '', 'mayacraft.net', 6807, '', '{
  "contentStyle": {
    "backgroundColor": "#3498db",
    "--card-bg": "#3498db"
  },
  "numberStyle": {
    "color": "#3498db"
  },
  "icon": "fas fa-database"
}', 1),
(3, '个人云盘', 'NextCloud存储空间', '192.168.100.109', 80, 'cloud', 'mayacraft.net', 6808, '', '{
  "contentStyle": {
    "backgroundColor": "#2ecc71",
    "--card-bg": "#2ecc71"
  },
  "numberStyle": {
    "color": "#2ecc71"
  },
  "icon": "fas fa-cloud"
}', 2),
(4, '家庭媒体中心', 'Plex媒体服务器', '192.168.100.110', 32400, '', 'mayacraft.net', 6809, '', '{
  "contentStyle": {
    "backgroundColor": "#e74c3c",
    "--card-bg": "#e74c3c"
  },
  "numberStyle": {
    "color": "#e74c3c"
  },
  "icon": "fas fa-film"
}', 3),
(5, '智能家居面板', 'Home Assistant控制', '192.168.100.111', 8123, '', 'mayacraft.net', 6810, '', '{
  "contentStyle": {
    "backgroundColor": "#f39c12",
    "--card-bg": "#f39c12"
  },
  "numberStyle": {
    "color": "#f39c12"
  },
  "icon": "fas fa-home"
}', 4),
(6, '监控系统', '安全摄像头监控', '192.168.100.112', 7080, '', 'mayacraft.net', 6811, '', '{
  "contentStyle": {
    "backgroundColor": "#34495e",
    "--card-bg": "#34495e"
  },
  "numberStyle": {
    "color": "#34495e"
  },
  "icon": "fas fa-video"
}', 5); 