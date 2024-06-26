REM 每日排程
REM 取得今天的年、月、日 (自動補零)%date = YYYY-MM-DD
SET TodayYear=%date:~0,4%
SET TodayMonthP0=%date:~5,2%
SET TodayDayP0=%date:~8,2%

REM 取得時、分、秒 、豪秒 %time = HH:mm:ss.ss
SET Hour=%time:~0,2%
SET Minute=%time:~3,2%

REM 排程 進行資料庫備份
echo %TodayYear%/%TodayMonth%/%TodayDay% %Hour%:%Minute% >> schedule-postgres-backup.log
curl -X post localhost/api/backstage/schedule.postgres-backup >> schedule-postgres-backup.log
echo. >> schedule-postgres-backup.log

robocopy "%LOCAL_DB_FILE%" "%REMOTE_DB_DIR_BK%" 
robocopy "%LOCAL_FILE%" "%REMOTE_DIR_BK%" 

REM 檢查WinSCP退出代碼
if %ERRORLEVEL% neq 0 (
    echo Error: WinSCP returned non-zero exit code %ERRORLEVEL%.
	pause
    exit /b %ERRORLEVEL%
)
"C:\Program Files (x86)\WinSCP\WinSCP.com" /log=winscp.log /command ^
    "option batch abort" ^
    "option confirm off" ^
    "open sftp://%USER%:%PASSWORD%@%HOST%" ^
	"synchronize remote -mirror %LOCAL_DB_FILE% %REMOTE_DB_DIR%" ^
	"synchronize remote -mirror %LOCAL_FILE% %REMOTE_DIR%" ^
    "exit"
REM 檢查WinSCP退出代碼
if %ERRORLEVEL% neq 0 (
    echo Error: WinSCP returned non-zero exit code %ERRORLEVEL%.
	pause
    exit /b %ERRORLEVEL%
)