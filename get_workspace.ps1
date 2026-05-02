$headers = @{
    "Authorization" = "Bearer 8341bb4c-512a-4960-a402-74283fc69835"
    "Content-Type" = "application/json"
}

$body = '{"query":"query { me { workspaces { id name } } }"}'

try {
    $response = Invoke-RestMethod -Uri "https://backboard.railway.com/graphql/v2" -Method Post -Headers $headers -Body $body
    Write-Host "Workspaces:" -ForegroundColor Cyan
    $response.data.me.workspaces | ForEach-Object {
        Write-Host "ID: $($_.id) - Name: $($_.name)" -ForegroundColor Green
    }
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
    if ($_.Exception.Response) {
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        Write-Host $reader.ReadToEnd()
    }
}
