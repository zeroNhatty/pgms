## Initial Idea
We can’t deny that as a developing country, our power grid isn’t that consistent. Reporting power faults takes hours, sometimes days, and most of that time is spent trying to pinpoint the exact location of the fault because there is no way to know which specific line or transformer has the problem. This project simulates an automatic monitoring and reporting system for power lines to ensure faster responses, less manual tracing, and better service overall.



**Functional Requirements**:

- The simulated nodes/emitters on power lines or neighborhood signal hubs must ping the system at regular intervals to verify their active connection.
- The system must maintain a high-frequency ledger of these incoming pings, tracking uptime histories for every registered grid node.
- If a specific node fails to ping the system for a predefined number of consecutive cycles, the system must flag that sector as "Inactive" and generate a high priority fault ticket.
- The frontend must render an interactive map showing the real time status of the grid. Active sectors render green, while sectors with open fault tickets automatically turn dark/red to isolate the failure point visually. (eg:
    ![Las Vegas Night Light Image](src/las-vegas-from-space.webp)
)
- The system must provide a public-facing report form allowing citizens to manually submit issues. The form will capture basic user details, geographic location/neighborhood, and the specific anomaly type (e.g., total blackout, low voltage/undervoltage, or high-voltage spikes).
- The backend should cross-reference manual public reports with automated node failures. If multiple users in the same perimeter report "undervoltage" while the node is still pinging, it flags the area for maintenance without dropping the node entirely. 
