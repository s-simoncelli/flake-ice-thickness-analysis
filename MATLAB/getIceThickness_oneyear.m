clc; clear; close all
% Get and export the icethickness in a TXT file for MySQL export

% from interpolateData_oneyear.m
file = '/Volumes/PTV #2/rda/ice_2019/out/interpData.mat';
outTXTFile = fullfile(fileparts(file), 'iceThickness.txt');
load(file);

newTimeVector = timeVector(1):timeVector(end);
unixTimeStamp = posixtime(newTimeVector);
t = days(newTimeVector - newTimeVector(1));
t = t' + 1; % vector starts from 0 [1-366]

Tf = 0;
a = 3.3; % [cm / (C d)]^0.5

%% Interpolate for time
totalRows = size(interpT, 1);
totalCols = size(interpT, 2);
totalTime = length(newTimeVector);
airTSeries = NaN(totalRows, totalCols, totalTime);
iceThickness = NaN(totalRows, totalCols, totalTime);

dlmwrite(outTXTFile, []); % create empty file
fileID = fopen(outTXTFile, 'a');
for px=1:totalRows
    fprintf('>> Row: %d/%d (%.2f%%)\n', px, totalRows, px/totalRows*100);
    for py=1:totalCols
        T = squeeze(interpT(px, py, :)) - 273.15;
        % fill missing T value
        airTSeries(px, py, :) = interp1(timeVector, T, newTimeVector);
        % if there are no value at the beggining of the time series (NaN), extrapolate
%         airTSeries = fillmissing(airTSeries, 'linear');

        % Get ice thickness
        S = (Tf - airTSeries(px, py, :));
        S(S < 0) = 0;
        S = squeeze(S);
        iceThickness(px, py, :) = round(a*sqrt(S.*t))/100;
        
        I = ones(totalTime, 1);
        data = [unixTimeStamp' I*latidueGrid(px, py) I*longitudeGrid(px, py) ...
            squeeze(airTSeries(px, py, :)) squeeze(iceThickness(px, py, :))];
        
        for k=1:totalTime
            fprintf(fileID, '%.0f,%.1f,%.1f,%.4f,%.6f\n', data(k, :));
        end
    end
end
fclose(fileID);

%% Export
save(fullfile(fileparts(file), 'iceThickness.mat'), 'airTSeries', 'iceThickness', ...
    'newTimeVector', 'latidueGrid', 'longitudeGrid');